<?php

namespace App\Jobs;

use App\Http\Controllers\Central\StripePaymentController;
use App\Models\Central\Payment;
use App\Services\Tenant\TenantProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FulfillStripePaymentAfterResponse implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $paymentId,
        public readonly ?string $webhookEventId = null,
    ) {}

    public function handle(
        TenantProvisioningService $provisioning,
        StripePaymentController $stripePayments,
    ): void {
        $lock = Cache::lock('stripe-payment-fulfillment:' . $this->paymentId, 1800);

        if (! $lock->get()) {
            return;
        }

        try {
            Log::info('Stripe payment fulfillment started.', [
                'payment_id' => $this->paymentId,
            ]);

            $payment = Payment::on('central')->findOrFail($this->paymentId);

            // A deploy may have stopped an older in-request provisioning attempt.
            // The queue lock guarantees there is no other current fulfillment job.
            $restaurant = $payment->restaurant()->with('tenantDatabase')->firstOrFail();
            if (
                $restaurant->status === 'provisioning'
                && $restaurant->tenantDatabase?->status === 'provisioning'
            ) {
                $restaurant->tenantDatabase->forceFill([
                    'status' => 'failed',
                    'last_error' => 'Previous provisioning attempt was interrupted; retrying from a clean tenant schema.',
                ])->save();
                $restaurant->forceFill(['status' => 'failed'])->save();
            }

            $stripePayments->fulfill($payment, $provisioning);

            Log::info('Stripe payment fulfillment completed.', [
                'payment_id' => $this->paymentId,
            ]);

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
