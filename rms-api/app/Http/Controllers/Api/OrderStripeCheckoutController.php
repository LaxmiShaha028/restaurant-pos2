<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Central\PaymentMethod;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;
use Throwable;

class OrderStripeCheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly \App\Services\PaymentService $payments,
    )
    {
    }

    public function checkout(StoreOrderRequest $request): JsonResponse
    {
        if (! config('services.stripe.secret') || ! config('services.stripe.key')) {
            return response()->json(['success' => false, 'message' => 'Stripe is not configured. Add STRIPE_KEY and STRIPE_SECRET to the API environment.'], 503);
        }
        abort_unless(PaymentMethod::on('central')->where('code', 'stripe')->where('status', true)->exists(), 422, 'Stripe is not enabled by Super Admin.');

        $payload = $request->validated();
        if (strtolower((string) ($payload['payment_method'] ?? '')) !== 'stripe') {
            throw ValidationException::withMessages(['payment_method' => ['Stripe checkout requires the Stripe payment method.']]);
        }
        $amount = round((float) ($payload['paid_amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['paid_amount' => ['A positive payment amount is required for Stripe checkout.']]);
        }
        $payload['paid_amount'] = 0;
        $payload['payment_reference'] = null;
        $payload['payment_method'] = null;

        // Let OrderService calculate the authoritative total before charging.
        $order = $this->orders->createOrder($payload, (int) $request->user()->id);
        if ($amount > (float) $order->due_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe amount exceeded the calculated order balance. The order was saved unpaid in Order Management.',
                'data' => ['order_id' => $order->id],
            ], 422);
        }

        try {
            $isPos = $request->input('checkout_context') === 'pos';
            $tableIds = collect($request->input('checkout_tables', []))
                ->map(fn ($id) => filter_var($id, FILTER_VALIDATE_INT))
                ->filter(fn ($id) => $id !== false && $id > 0)->unique()->values();
            $returnUrl = rtrim(config('app.frontend_url'), '/').($isPos ? '/pos-order/create' : '/orders/create');
            if ($isPos && $tableIds->isNotEmpty()) {
                $returnUrl .= '?tables='.rawurlencode($tableIds->implode(','));
            }
            $checkoutParams = [
                'mode' => 'payment',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower((string) config('app.currency', 'bdt')),
                        'unit_amount' => (int) round($amount * 100),
                        'product_data' => ['name' => 'Restaurant order payment'],
                    ],
                ]],
                'metadata' => ['tenant_order_checkout' => '1'],
                'success_url' => $returnUrl.(str_contains($returnUrl, '?') ? '&' : '?').'stripe_order_session={CHECKOUT_SESSION_ID}',
                'cancel_url' => $returnUrl.(str_contains($returnUrl, '?') ? '&' : '?').'stripe_order_cancelled=1',
            ];
            $customerEmail = trim((string) ($payload['customer_email'] ?? ''));
            if ($customerEmail !== '' && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $checkoutParams['customer_email'] = $customerEmail;
            }

            $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->create($checkoutParams);

            DB::table('order_stripe_checkouts')->insert([
                'checkout_session_id' => $session->id,
                'amount' => $amount,
                'currency' => strtolower((string) config('app.currency', 'bdt')),
                'status' => 'pending',
                'created_by' => (int) $request->user()->id,
                'order_id' => $order->id,
                'order_payload' => json_encode([], JSON_THROW_ON_ERROR),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'data' => ['checkout_url' => $session->url]]);
        } catch (Throwable $exception) {
            report($exception);
            $message = app()->isLocal()
                ? 'Stripe Checkout could not start: '.$exception->getMessage()
                : 'Unable to start Stripe Checkout.';
            return response()->json(['success' => false, 'message' => $message.' The order was saved unpaid in Order Management.'], 502);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate(['session_id' => ['required', 'string', 'max:255']]);
        if (! config('services.stripe.secret')) {
            return response()->json(['success' => false, 'message' => 'Stripe is not configured.'], 503);
        }

        $checkout = DB::table('order_stripe_checkouts')
            ->where('checkout_session_id', $data['session_id'])
            ->where('created_by', (int) $request->user()->id)
            ->first();
        abort_unless($checkout, 404, 'Stripe checkout was not found.');

        if ($checkout->order_id && $checkout->status === 'paid') {
            $order = \App\Models\Order::with(['customer', 'primaryTable', 'tables', 'items', 'payments', 'creator'])->findOrFail($checkout->order_id);
            return response()->json(['success' => true, 'data' => new OrderResource($order)]);
        }
        abort_unless($checkout->status === 'pending', 422, 'This Stripe checkout cannot be confirmed.');

        $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->retrieve($checkout->checkout_session_id);
        abort_unless(($session->payment_status ?? null) === 'paid', 422, 'Stripe has not confirmed payment yet.');
        abort_unless((int) $session->amount_total === (int) round((float) $checkout->amount * 100)
            && strtolower((string) $session->currency) === strtolower((string) $checkout->currency), 422, 'Stripe payment amount did not match.');

        $order = DB::transaction(function () use ($checkout, $session): \App\Models\Order {
            $locked = DB::table('order_stripe_checkouts')->where('id', $checkout->id)->lockForUpdate()->first();
            if ($locked->status === 'paid' && $locked->order_id) {
                return \App\Models\Order::findOrFail($locked->order_id);
            }
            abort_unless($locked->status === 'pending', 422, 'This Stripe checkout cannot be confirmed.');
            $order = \App\Models\Order::query()->findOrFail($locked->order_id);
            $this->payments->addPayment($order, [
                'amount' => $locked->amount,
                'payment_method' => 'stripe',
                'reference' => (string) ($session->payment_intent ?? $session->id),
                'note' => 'Stripe Checkout payment.',
            ]);
            DB::table('order_stripe_checkouts')->where('id', $locked->id)->update([
                'status' => 'paid', 'payment_intent_id' => (string) ($session->payment_intent ?? ''),
                'updated_at' => now(),
            ]);
            return $order->fresh();
        });

        $order->load(['customer', 'primaryTable', 'tables', 'items', 'payments', 'creator']);
        return response()->json(['success' => true, 'message' => 'Stripe payment confirmed.', 'data' => new OrderResource($order)]);
    }
}
