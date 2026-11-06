<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Payment;
use App\Models\Central\PaymentMethod;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionPlan;
use App\Services\Tenant\TenantProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

class StripePaymentController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_type' => ['required', 'in:initial,upgrade,renewal,plan_update'],
            'restaurant_id' => ['required', 'integer'],
            'subscription_id' => ['required', 'integer'],
            'subscription_plan_id' => ['nullable', 'integer'],
        ]);

        if (! config('services.stripe.secret') || ! config('services.stripe.key')) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe test keys are not configured. Add STRIPE_KEY and STRIPE_SECRET to the API environment.',
            ], 503);
        }

        if (! PaymentMethod::on('central')->where('code', 'stripe')->where('status', true)->exists()) {
            return response()->json(['success' => false, 'message' => 'Stripe is not enabled as a payment method.'], 422);
        }

        $restaurantModel = Restaurant::on('central')->findOrFail($data['restaurant_id']);
        $subscription = Subscription::on('central')->with('plan')
            ->where('restaurant_id', $restaurantModel->id)->findOrFail($data['subscription_id']);
        $type = $data['payment_type'];

        if ($type === 'initial' && ($restaurantModel->status !== 'pending' || $subscription->status !== 'pending')) {
            return response()->json(['success' => false, 'message' => 'This restaurant is not awaiting its initial payment.'], 422);
        }
        if ($type === 'upgrade' && (! $subscription->trial_end_at || ! in_array($subscription->status, ['active', 'expired'], true))) {
            return response()->json(['success' => false, 'message' => 'This subscription is not eligible for a paid trial upgrade.'], 422);
        }
        if ($type === 'renewal' && ! in_array($subscription->status, ['active', 'expired', 'cancelled'], true)) {
            return response()->json(['success' => false, 'message' => 'This subscription is not eligible for renewal.'], 422);
        }
        if ($type === 'plan_update' && $subscription->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Only active subscriptions can change plans.'], 422);
        }

        $plan = $type === 'upgrade'
            ? SubscriptionPlan::on('central')->where('is_active', true)->findOrFail($data['subscription_plan_id'] ?? 0)
            : $subscription->plan;
        abort_unless($plan && (float) $plan->price > 0, 422, 'A paid subscription plan is required.');

        $currency = strtolower((string) $plan->currency);
        if (! preg_match('/^[a-z]{3}$/', $currency)) {
            return response()->json(['success' => false, 'message' => 'The plan currency is not supported by Stripe.'], 422);
        }

        $periodStart = now();
        $base = $type === 'renewal' && $subscription->status === 'active' && $subscription->end_at && $subscription->end_at->isFuture()
            ? $subscription->end_at->copy()
            : $periodStart->copy();
        $periodEnd = $base->copy();
        $intervalCount = max(1, (int) ($plan->billing_interval_count ?: 1));
        match (strtolower((string) $plan->billing_interval)) {
            'day', 'days' => $periodEnd->addDays($intervalCount),
            'week', 'weeks' => $periodEnd->addWeeks($intervalCount),
            'year', 'years' => $periodEnd->addYears($intervalCount),
            default => $periodEnd->addMonths($intervalCount),
        };

        $payment = Payment::on('central')->create([
            'restaurant_id' => $restaurantModel->id,
            'subscription_id' => $subscription->id,
            'gateway' => 'stripe',
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'payment_method' => 'stripe',
            'status' => 'pending',
            'gateway_response' => [
                'type' => 'stripe', 'payment_type' => $type,
                'plan_id' => $plan->id, 'period_start' => $base->toIso8601String(),
                'period_end' => $periodEnd->toIso8601String(),
            ],
        ]);

        try {
            $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->create([
                'mode' => 'payment',
                'customer_email' => $restaurantModel->owner_email,
                'invoice_creation' => ['enabled' => true],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => (int) round((float) $plan->price * 100),
                        'product_data' => ['name' => $plan->name.' subscription'],
                    ],
                ]],
                'metadata' => ['payment_id' => (string) $payment->id, 'payment_type' => $type],
                'success_url' => rtrim(config('app.frontend_url'), '/').'/super-admin/restaurants/'.$restaurantModel->id.'?stripe_payment=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim(config('app.frontend_url'), '/').'/super-admin/restaurants/'.$restaurantModel->id.'?stripe_payment=cancelled',
            ]);

            $payment->forceFill([
                'transaction_id' => $session->id,
                'gateway_response' => array_merge($payment->gateway_response ?? [], ['checkout_session_id' => $session->id]),
            ])->save();

            return response()->json(['success' => true, 'data' => ['checkout_url' => $session->url]]);
        } catch (Throwable $exception) {
            report($exception);
            $payment->forceFill(['status' => 'failed'])->save();
            return response()->json(['success' => false, 'message' => 'Unable to start Stripe Checkout. Check the Stripe test configuration.'], 502);
        }
    }

    public function webhook(Request $request, TenantProvisioningService $provisioning): JsonResponse
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $exception) {
            return response()->json(['message' => 'Invalid Stripe webhook signature.'], 400);
        }

        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object;
        if (($session->payment_status ?? null) !== 'paid') {
            return response()->json(['received' => true]);
        }

        $paymentId = (int) ($session->metadata->payment_id ?? 0);
        $payment = Payment::on('central')->find($paymentId);
        abort_unless($payment && $payment->gateway === 'stripe' && $payment->transaction_id === $session->id, 404);

        $inserted = DB::connection('central')->table('payment_webhooks')->insertOrIgnore([
            'payment_id' => $payment->id, 'gateway' => 'stripe', 'event_id' => $event->id,
            'event_type' => $event->type, 'payload' => json_encode($event->toArray()),
            'signature_verified' => true, 'status' => 'processing',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        if (! $inserted) {
            $existingEvent = DB::connection('central')->table('payment_webhooks')
                ->where('gateway', 'stripe')->where('event_id', $event->id)->first();
            if ($existingEvent?->status === 'processed') {
                return response()->json(['received' => true]);
            }
            DB::connection('central')->table('payment_webhooks')->where('gateway', 'stripe')->where('event_id', $event->id)->update([
                'payment_id' => $payment->id, 'event_type' => $event->type,
                'payload' => json_encode($event->toArray()), 'signature_verified' => true,
                'status' => 'processing', 'error_message' => null, 'updated_at' => now(),
            ]);
        }

        try {
            $expectedAmount = (int) round((float) $payment->amount * 100);
            if ((int) $session->amount_total !== $expectedAmount || strtolower($payment->currency) !== strtolower($session->currency)) {
                throw new \RuntimeException('Stripe amount or currency did not match the payment record.');
            }

            $payment->forceFill([
                'status' => 'paid', 'paid_at' => now(),
                'reference' => $session->payment_intent ?? $session->id,
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'stripe_payment_intent' => $session->payment_intent,
                    'checkout_session_id' => $session->id,
                    'stripe_invoice_id' => $session->invoice ?? null,
                ]),
            ])->save();

            $this->fulfill($payment, $provisioning);
            $this->syncStripeDocuments($payment, $session);
            DB::connection('central')->table('payment_webhooks')->where('gateway', 'stripe')->where('event_id', $event->id)->update([
                'status' => 'processed', 'processed_at' => now(), 'updated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            DB::connection('central')->table('payment_webhooks')->where('gateway', 'stripe')->where('event_id', $event->id)->update([
                'status' => 'failed', 'error_message' => $exception->getMessage(), 'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Payment fulfillment failed.'], 500);
        }

        return response()->json(['received' => true]);
    }

    public function confirm(Request $request, TenantProvisioningService $provisioning): JsonResponse
    {
        $data = $request->validate(['session_id' => ['required', 'string', 'max:255']]);
        $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->retrieve(
            $data['session_id'],
            ['expand' => ['invoice']]
        );
        abort_unless(($session->payment_status ?? null) === 'paid', 422, 'Stripe has not confirmed this payment yet.');
        $payment = Payment::on('central')->where('gateway', 'stripe')->where('transaction_id', $session->id)->findOrFail((int) ($session->metadata->payment_id ?? 0));

        $expectedAmount = (int) round((float) $payment->amount * 100);
        abort_unless((int) $session->amount_total === $expectedAmount && strtolower($payment->currency) === strtolower($session->currency), 422, 'Stripe payment amount did not match.');
        if ($payment->status !== 'paid') {
            $payment->forceFill(['status' => 'paid', 'paid_at' => now(), 'reference' => $session->payment_intent ?? $session->id])->save();
        }

        // Retry fulfillment if payment was paid but tenant provisioning failed previously.
        $this->fulfill($payment, $provisioning);

        $this->syncStripeDocuments($payment, $session);

        $meta = $payment->gateway_response ?? [];
        $admin = null;
        if (! empty($meta['admin_password_encrypted'])) {
            $admin = [
                'username' => $meta['admin_username'] ?? null,
                'password' => Crypt::decryptString($meta['admin_password_encrypted']),
            ];
            unset($meta['admin_username'], $meta['admin_password_encrypted']);
            $payment->forceFill(['gateway_response' => $meta])->save();
        }

        return response()->json(['success' => true, 'message' => 'Stripe payment confirmed.', 'data' => ['payment' => $payment->fresh(), 'admin' => $admin]]);
    }

    public function documents(int $paymentId): JsonResponse
    {
        $payment = Payment::on('central')->where('gateway', 'stripe')->findOrFail($paymentId);
        abort_unless($payment->status === 'paid', 422, 'Documents are available after payment succeeds.');

        $client = new StripeClient(config('services.stripe.secret'));
        $meta = $payment->gateway_response ?? [];
        $sessionId = $meta['checkout_session_id'] ?? $payment->transaction_id;
        $session = $client->checkout->sessions->retrieve($sessionId, ['expand' => ['invoice']]);
        $this->syncStripeDocuments($payment, $session);

        $meta = $payment->fresh()->gateway_response ?? [];
        return response()->json([
            'success' => true,
            'data' => [
                'invoice_url' => $meta['stripe_invoice_url'] ?? $meta['stripe_invoice_pdf'] ?? null,
                'receipt_url' => $meta['stripe_receipt_url'] ?? null,
            ],
        ]);
    }

    private function fulfill(Payment $payment, TenantProvisioningService $provisioning): void
    {
        $meta = $payment->gateway_response ?? [];
        $type = $meta['payment_type'] ?? '';
        $restaurant = Restaurant::on('central')->findOrFail($payment->restaurant_id);
        $subscription = Subscription::on('central')->with('plan')->findOrFail($payment->subscription_id);

        if ($type === 'initial') {
            if (
                $subscription->status === 'pending'
                && in_array($restaurant->status, ['pending', 'failed', 'provisioning'], true)
            ) {
                $result = $provisioning->provisionExistingRestaurant($restaurant);
                $meta['admin_username'] = $result['admin']['username'];
                $meta['admin_password_encrypted'] = Crypt::encryptString($result['admin']['password']);
            }
        } elseif (in_array($type, ['upgrade', 'renewal', 'plan_update'], true)) {
            $plan = SubscriptionPlan::on('central')->findOrFail((int) $meta['plan_id']);
            $subscription->forceFill([
                'subscription_plan_id' => $plan->id, 'status' => 'active',
                'start_at' => $meta['period_start'], 'end_at' => $meta['period_end'],
                'cancelled_at' => null, 'cancel_reason' => null,
            ])->save();
            $restaurant->forceFill(['status' => 'active'])->save();
        }

        $payment->forceFill(['gateway_response' => $meta])->save();
    }

    private function syncStripeDocuments(Payment $payment, object $session): void
    {
        $meta = $payment->gateway_response ?? [];
        $sessionInvoice = $session->invoice ?? null;
        $invoiceId = is_object($sessionInvoice)
            ? ($sessionInvoice->id ?? null)
            : ($sessionInvoice ?: ($meta['stripe_invoice_id'] ?? null));
        $documents = [];

        if ($invoiceId) {
            $documents['stripe_invoice_id'] = $invoiceId;
            try {
                $invoice = (new StripeClient(config('services.stripe.secret')))->invoices->retrieve($invoiceId);
                $documents['stripe_invoice_url'] = $invoice->hosted_invoice_url;
                $documents['stripe_invoice_pdf'] = $invoice->invoice_pdf;
                $documents['stripe_receipt_url'] = $invoice->hosted_invoice_url;
            } catch (Throwable $exception) {
                report($exception);
            }
        } elseif (! empty($session->payment_intent)) {
            try {
                $intent = (new StripeClient(config('services.stripe.secret')))->paymentIntents->retrieve(
                    $session->payment_intent,
                    ['expand' => ['latest_charge']]
                );
                $charge = $intent->latest_charge ?? null;
                if (is_object($charge)) {
                    $documents['stripe_receipt_url'] = $charge->receipt_url ?? null;
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if ($documents) {
            $payment->forceFill(['gateway_response' => array_merge($meta, $documents)])->save();
        }
    }
}
