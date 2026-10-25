<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\RecipeMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemandForecastService
{
    private const HISTORY_DAYS = 56;
    private const RECENT_DAYS = 28;

    public function build(int $forecastDays = 7): array
    {
        $today = Carbon::today();
        $historyStart = $today->copy()->subDays(self::HISTORY_DAYS);
        $historyEnd = $today->copy()->subDay()->endOfDay();

        $historyRows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', [
                Order::STATUS_PENDING,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
                Order::STATUS_SERVED,
                Order::STATUS_COMPLETED,
            ])
            ->whereBetween('orders.created_at', [$historyStart->copy()->startOfDay(), $historyEnd])
            ->selectRaw('DATE(orders.created_at) AS sold_on')
            ->addSelect('order_items.menu_item_id', 'order_items.menu_item_variant_id')
            ->selectRaw('MAX(order_items.item_name) AS item_name')
            ->selectRaw('SUM(order_items.quantity) AS quantity_sold')
            ->groupBy('sold_on', 'order_items.menu_item_id', 'order_items.menu_item_variant_id')
            ->get();

        $openSalesDates = $historyRows
            ->pluck('sold_on')
            ->map(fn ($date) => (string) $date)
            ->unique()
            ->values();

        $salesByTarget = [];
        $recentSalesByMenu = [];
        $recentStart = $today->copy()->subDays(self::RECENT_DAYS)->toDateString();

        foreach ($historyRows as $row) {
            $menuItemId = (int) ($row->menu_item_id ?? 0);
            if ($menuItemId <= 0) {
                continue;
            }

            $variantId = $row->menu_item_variant_id !== null
                ? (int) $row->menu_item_variant_id
                : null;
            $targetKey = $this->targetKey($menuItemId, $variantId);
            $quantity = (float) $row->quantity_sold;
            $salesByTarget[$targetKey]['menu_item_id'] = $menuItemId;
            $salesByTarget[$targetKey]['variant_id'] = $variantId;
            $salesByTarget[$targetKey]['item_name'] = (string) $row->item_name;
            $salesByTarget[$targetKey]['daily'][$row->sold_on] = $quantity;

            if ((string) $row->sold_on >= $recentStart) {
                $recentSalesByMenu[$menuItemId]['menu_item_id'] = $menuItemId;
                $recentSalesByMenu[$menuItemId]['item_name'] = (string) $row->item_name;
                $recentSalesByMenu[$menuItemId]['quantity_sold'] =
                    ($recentSalesByMenu[$menuItemId]['quantity_sold'] ?? 0) + $quantity;
            }
        }

        $forecastDates = collect(range(1, $forecastDays))
            ->map(fn (int $offset) => $today->copy()->addDays($offset));
        $forecasts = [];
        $dailyItems = [];

        foreach ($forecastDates as $date) {
            $matchingHistoryDates = $openSalesDates
                ->filter(function (string $historyDate) use ($date): bool {
                    $historyDay = Carbon::parse($historyDate);
                    return $historyDay->dayOfWeek === $date->dayOfWeek;
                });
            $sampleCount = $matchingHistoryDates->count();
            $items = [];

            foreach ($salesByTarget as $sales) {
                $total = 0.0;
                foreach ($matchingHistoryDates as $historyDate) {
                    $total += (float) ($sales['daily'][$historyDate] ?? 0);
                }

                $quantity = $sampleCount > 0 ? round($total / $sampleCount, 2) : 0.0;
                if ($quantity <= 0) {
                    continue;
                }

                $forecast = [
                    'menu_item_id' => $sales['menu_item_id'],
                    'variant_id' => $sales['variant_id'],
                    'item_name' => $sales['item_name'],
                    'forecast_quantity' => $quantity,
                    'history_days_used' => $sampleCount,
                ];
                $items[] = $forecast;
                $key = $this->targetKey($sales['menu_item_id'], $sales['variant_id']);
                $dailyItems[$key]['menu_item_id'] = $sales['menu_item_id'];
                $dailyItems[$key]['variant_id'] = $sales['variant_id'];
                $dailyItems[$key]['item_name'] = $sales['item_name'];
                $dailyItems[$key]['forecast_quantity'] =
                    ($dailyItems[$key]['forecast_quantity'] ?? 0) + $quantity;
            }

            $forecasts[] = [
                'date' => $date->toDateString(),
                'history_days_used' => $sampleCount,
                'items' => $items,
            ];
        }

        return [
            'meta' => [
                'forecast_days' => $forecastDays,
                'history_days' => self::HISTORY_DAYS,
                'history_from' => $historyStart->toDateString(),
                'history_to' => $today->copy()->subDay()->toDateString(),
                'history_days_used' => $openSalesDates->count(),
                'method' => 'average_sales_on_matching_weekday',
            ],
            'forecast' => $forecasts,
            'popular_items' => collect($recentSalesByMenu)
                ->sortByDesc('quantity_sold')
                ->take(10)
                ->values()
                ->map(fn (array $item) => [
                    ...$item,
                    'quantity_sold' => round((float) $item['quantity_sold'], 2),
                ])
                ->all(),
            'peak_hours' => $this->getPeakHours($today),
            'ingredient_recommendations' => $this->getIngredientRecommendations($dailyItems),
        ];
    }

    private function getPeakHours(Carbon $today): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%H', orders.created_at) AS INTEGER)",
            'pgsql' => 'EXTRACT(HOUR FROM orders.created_at)',
            default => 'HOUR(orders.created_at)',
        };
        $start = $today->copy()->subDays(self::RECENT_DAYS)->startOfDay();
        $end = $today->copy()->subDay()->endOfDay();

        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', [
                Order::STATUS_PENDING,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
                Order::STATUS_SERVED,
                Order::STATUS_COMPLETED,
            ])
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("{$hourExpression} AS sales_hour")
            ->selectRaw('SUM(order_items.quantity) AS quantity_sold')
            ->selectRaw('COUNT(DISTINCT orders.id) AS order_count')
            ->groupBy('sales_hour')
            ->orderByDesc('quantity_sold')
            ->limit(24)
            ->get()
            ->map(fn ($row) => [
                'hour' => (int) $row->sales_hour,
                'quantity_sold' => round((float) $row->quantity_sold, 2),
                'order_count' => (int) $row->order_count,
            ])
            ->all();
    }

    private function getIngredientRecommendations(array $dailyItems): array
    {
        if ($dailyItems === []) {
            return [];
        }

        $mappings = RecipeMapping::query()
            ->with(['rawMaterial.restaurantStock'])
            ->whereNotNull('menu_item_id')
            ->whereNull('add_on_id')
            ->get();
        $mappingsByTarget = $mappings->groupBy(fn (RecipeMapping $mapping) =>
            $this->targetKey((int) $mapping->menu_item_id, $mapping->variant_id !== null ? (int) $mapping->variant_id : null)
        );

        $ingredientNeeds = [];
        $unmappedItems = [];

        foreach ($dailyItems as $item) {
            $variantKey = $this->targetKey($item['menu_item_id'], $item['variant_id']);
            $defaultKey = $this->targetKey($item['menu_item_id'], null);
            $recipe = $mappingsByTarget->get($variantKey);
            if ($recipe === null || $recipe->isEmpty()) {
                $recipe = $mappingsByTarget->get($defaultKey);
            }

            if ($recipe === null || $recipe->isEmpty()) {
                $unmappedItems[$item['menu_item_id']] = [
                    'menu_item_id' => $item['menu_item_id'],
                    'item_name' => $item['item_name'],
                ];
                continue;
            }

            foreach ($recipe as $mapping) {
                $material = $mapping->rawMaterial;
                if (!$material || !$material->is_active) {
                    continue;
                }

                $baseUnit = strtolower(trim((string) $material->base_unit));
                $recipeUnit = strtolower(trim((string) $mapping->unit));
                $unitMatches = $baseUnit !== '' && $baseUnit === $recipeUnit;
                $id = (int) $material->id;
                $ingredientNeeds[$id]['raw_material_id'] = $id;
                $ingredientNeeds[$id]['material_name'] = (string) $material->material_name;
                $ingredientNeeds[$id]['unit'] = (string) $material->base_unit;
                $ingredientNeeds[$id]['forecast_usage'] =
                    ($ingredientNeeds[$id]['forecast_usage'] ?? 0)
                    + ((float) $mapping->quantity * (float) $item['forecast_quantity']);
                $ingredientNeeds[$id]['unit_mismatch'] =
                    ($ingredientNeeds[$id]['unit_mismatch'] ?? false) || !$unitMatches;
                $ingredientNeeds[$id]['stock'] = $material->restaurantStock;
                $ingredientNeeds[$id]['minimum_quantity'] = (float) $material->restaurant_minimum_quantity;
            }
        }

        $pendingByMaterial = $this->getPendingPurchaseQuantities(array_keys($ingredientNeeds));

        $recommendations = collect($ingredientNeeds)
            ->map(function (array $need) use ($pendingByMaterial): array {
                $stock = $need['stock'];
                $onOrder = (float) ($pendingByMaterial[$need['raw_material_id']] ?? 0);
                $current = (float) ($stock?->quantity ?? 0);
                $minimum = (float) $need['minimum_quantity'];
                $usage = round((float) $need['forecast_usage'], 4);
                $purchase = $need['unit_mismatch']
                    ? null
                    : round(max(0, $usage + $minimum - $current - $onOrder), 4);

                return [
                    'raw_material_id' => $need['raw_material_id'],
                    'material_name' => $need['material_name'],
                    'unit' => $need['unit'],
                    'forecast_usage' => $usage,
                    'current_stock' => round($current, 4),
                    'minimum_stock' => round($minimum, 4),
                    'incoming_quantity' => round($onOrder, 4),
                    'recommended_purchase_quantity' => $purchase,
                    'unit_mismatch' => $need['unit_mismatch'],
                ];
            })
            ->sortByDesc(fn (array $row) => (float) ($row['recommended_purchase_quantity'] ?? -1))
            ->values()
            ->all();

        return [
            'items' => $recommendations,
            'unmapped_menu_items' => array_values($unmappedItems),
        ];
    }

    private function getPendingPurchaseQuantities(array $rawMaterialIds): array
    {
        if ($rawMaterialIds === []) {
            return [];
        }

        $orders = PurchaseOrder::query()
            ->whereIn('status', [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_PARTIAL])
            ->with(['items' => fn ($query) => $query->whereIn('raw_material_id', $rawMaterialIds)])
            ->get();
        $pending = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $id = (int) $item->raw_material_id;
                $pending[$id] = ($pending[$id] ?? 0) + max(0, (float) $item->remaining_quantity);
            }
        }

        return $pending;
    }

    private function targetKey(int $menuItemId, ?int $variantId): string
    {
        return $menuItemId . ':' . ($variantId ?? 0);
    }
}
