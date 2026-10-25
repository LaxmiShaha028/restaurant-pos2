<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PickupController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function board(): JsonResponse
    {
        $orders = Order::query()
            ->where('fulfillment_type', 'takeaway')
            ->where('pickup_status', 'uncollected')
            ->whereIn('status', [Order::STATUS_PREPARING, Order::STATUS_READY])
            ->with(['items:id,order_id,item_name,variant_name,quantity'])
            ->orderByRaw("CASE WHEN status = 'ready' THEN 0 ELSE 1 END")
            ->orderBy('ready_at')
            ->orderBy('created_at')
            ->get()
            ->map(static fn (Order $order): array => [
                'id' => (int) $order->id,
                'token' => $order->pickup_token,
                'status' => $order->status,
                'created_at' => $order->created_at?->toISOString(),
                'ready_at' => $order->ready_at?->toISOString(),
                'items' => $order->items->map(static fn ($item): array => [
                    'name' => $item->item_name,
                    'variant' => $item->variant_name,
                    'quantity' => (int) $item->quantity,
                ])->values(),
            ])->values();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function markCollected(Order $order): JsonResponse
    {
        $updated = DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->fulfillment_type !== 'takeaway' || !$lockedOrder->pickup_token) {
                throw ValidationException::withMessages([
                    'order' => ['Only takeaway orders can be collected at the pickup counter.'],
                ]);
            }

            if ($lockedOrder->pickup_status === 'collected') {
                return $lockedOrder;
            }

            if ($lockedOrder->status !== Order::STATUS_READY) {
                throw ValidationException::withMessages([
                    'status' => ['Only ready takeaway orders can be marked collected.'],
                ]);
            }

            // Serving through the existing order workflow keeps kitchen batch,
            // inventory consumption and order status synchronized.
            return $this->orderService->updateStatus($lockedOrder, Order::STATUS_SERVED);
        });

        return response()->json([
            'success' => true,
            'message' => 'Takeaway order marked as collected.',
            'data' => [
                'id' => (int) $updated->id,
                'status' => $updated->status,
                'pickup_status' => $updated->pickup_status,
                'picked_up_at' => $updated->picked_up_at?->toISOString(),
            ],
        ]);
    }
}
