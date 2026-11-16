<?php

namespace App\Jobs;

use App\Http\Controllers\Central\StripePaymentController;
use App\Models\Central\Payment;
use App\Services\Tenant\TenantProvisioningService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FulfillStripePaymentAfterResponse
{
    public function __construct(
        public readonly int $paymentId,
        public readonly ?string $webhookEventId = null,
    ) {}

    public function handle(
        TenantProvisioningService $provisioning,
        StripePaymentController $stripePayments,
    ): void {
        $lock = Cache::lock('stripe-payment-fulfillment:' . $this->paymentId, 900);

        if (! $lock->get()) {
            return;
        }

        try {
            $payment = Payment::on('central')->findOrFail($this->paymentId);
            $stripePayments->fulfill($payment, $provisioning);

            if ($this->webhookEventId !== null) {
                DB::connection('central')->table('payment_webhooks')
                    ->where('gateway', 'stripe')
                    ->where('event_id', $this->webhookEventId)
                    ->update([
                        'status' => 'processed',
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        } catch (Throwable $exception) {
            Log::error('Stripe payment fulfillment failed after response.', [
                'payment_id' => $this->paymentId,
                'webhook_event_id' => $this->webhookEventId,
                'exception' => $exception,
            ]);

            if ($this->webhookEventId !== null) {
                DB::connection('central')->table('payment_webhooks')
                    ->where('gateway', 'stripe')
                    ->where('event_id', $this->webhookEventId)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $exception->getMessage(),
                        'updated_at' => now(),
                    ]);
            }
        } finally {
            $lock->release();
        }
    }
}
