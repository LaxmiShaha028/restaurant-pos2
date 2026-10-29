<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OfferRequest;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(): JsonResponse
    {
        $offers = Offer::query()
            ->withCount(['orders as redemption_count' => fn ($query) => $query->where('status', '!=', Order::STATUS_CANCELED)])
            ->withSum(['orders as discount_total' => fn ($query) => $query->where('status', '!=', Order::STATUS_CANCELED)], 'offer_discount_amount')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Offers loaded successfully.',
            'data' => $offers,
            'summary' => [
                'total_offers' => $offers->count(),
                'active_offers' => $offers->where('is_active', true)->count(),
                'redemptions' => (int) $offers->sum('redemption_count'),
                'discount_total' => round((float) $offers->sum('discount_total'), 2),
            ],
        ]);
    }

    public function store(OfferRequest $request): JsonResponse
    {
        $offer = Offer::query()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Offer created successfully.',
            'data' => $offer,
        ], 201);
    }

    public function update(OfferRequest $request, Offer $offer): JsonResponse
    {
        $offer->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Offer updated successfully.',
            'data' => $offer->fresh(),
        ]);
    }

    public function destroy(Offer $offer): JsonResponse
    {
        if ($offer->orders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This offer has order history. Deactivate it instead of deleting it.',
                'data' => null,
                'errors' => null,
            ], 409);
        }

        $offer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offer deleted successfully.',
            'data' => null,
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $rows = Order::query()
            ->select('offer_id')
            ->selectRaw('COUNT(*) as redemptions')
            ->selectRaw('COALESCE(SUM(offer_discount_amount), 0) as discount_total')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->whereNotNull('offer_id')
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->groupBy('offer_id')
            ->get()
            ->keyBy('offer_id');

        $offers = Offer::query()->orderBy('name')->get()->map(function (Offer $offer) use ($rows): array {
            $row = $rows->get($offer->id);

            return [
                'offer_id' => (int) $offer->id,
                'name' => $offer->name,
                'type' => $offer->type,
                'redemptions' => (int) ($row?->redemptions ?? 0),
                'discount_total' => round((float) ($row?->discount_total ?? 0), 2),
                'net_sales' => round((float) ($row?->net_sales ?? 0), 2),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Offer performance report loaded successfully.',
            'data' => [
                'offers' => $offers,
                'totals' => [
                    'redemptions' => (int) $offers->sum('redemptions'),
                    'discount_total' => round((float) $offers->sum('discount_total'), 2),
                    'net_sales' => round((float) $offers->sum('net_sales'), 2),
                ],
            ],
        ]);
    }
}
