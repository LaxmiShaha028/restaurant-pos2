<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\PurchaseOrder;
use App\Models\RecipeMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemandForecastService
{
    private const BUSINESS_TIMEZONE = 'Asia/Dhaka';
    private const HISTORY_DAYS = 56;
    private const RECENT_DAYS = 28;

    public function build(int $forecastDays = 7, int $chartDays = 28): array
    {
        // Orders are persisted as UTC timestamps; report days follow the restaurant's Dhaka business day.
        $today = Carbon::today(self::BUSINESS_TIMEZONE);
        $historyStart = $today->copy()->subDays(self::HISTORY_DAYS);
        $queryStart = $today->copy()->subDays(self::HISTORY_DAYS + $chartDays);
        $historyEnd = $today->copy()->subDay()->endOfDay();
        $historyDates = collect();
        for ($date = $historyStart->copy(); $date->lte($historyEnd); $date->addDay()) {
            $historyDates->push($date->toDateString());
        }
        $dateExpression = $this->businessDateExpression();

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
            ->whereBetween('orders.created_at', [$queryStart->copy()->startOfDay()->utc(), $historyEnd->copy()->utc()])
            ->selectRaw("{$dateExpression} AS sold_on")
            ->addSelect('order_items.menu_item_id', 'order_items.menu_item_variant_id')
            ->selectRaw('MAX(order_items.item_name) AS item_name')
            ->selectRaw('SUM(order_items.quantity) AS quantity_sold')
            ->groupBy('sold_on', 'order_items.menu_item_id', 'order_items.menu_item_variant_id')
            ->get();

        $openSalesDates = $historyRows->pluck('sold_on')->map(fn ($date) => (string) $date)
            ->filter(fn (string $date) => $date >= $historyStart->toDateString())
            ->unique()->values();

        $salesByTarget = [];
        $salesByAddon = [];
        $recentSalesByMenu = [];
        $chartStart = $today->copy()->subDays($chartDays)->toDateString();

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

            if ((string) $row->sold_on >= $chartStart) {
                $recentSalesByMenu[$menuItemId]['menu_item_id'] = $menuItemId;
                $recentSalesByMenu[$menuItemId]['item_name'] = (string) $row->item_name;
                $recentSalesByMenu[$menuItemId]['quantity_sold'] =
                    ($recentSalesByMenu[$menuItemId]['quantity_sold'] ?? 0) + $quantity;
            }
        }

        $addonHistoryRows = OrderItemAddon::query()
            ->join('order_items', 'order_items.id', '=', 'order_item_addons.order_item_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', [Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY, Order::STATUS_SERVED, Order::STATUS_COMPLETED])
            ->whereBetween('orders.created_at', [$queryStart->copy()->startOfDay()->utc(), $historyEnd->copy()->utc()])
            ->selectRaw("{$dateExpression} AS sold_on")
            ->addSelect('order_item_addons.menu_addon_id')
            ->selectRaw('SUM(order_item_addons.quantity) AS quantity_sold')
            ->addSelect('order_item_addons.addon_name')
            ->groupBy('sold_on', 'order_item_addons.menu_addon_id', 'order_item_addons.addon_name')
            ->get();

        foreach ($addonHistoryRows as $row) {
            $addonId = $row->menu_addon_id !== null ? (int) $row->menu_addon_id : null;
            $addonName = (string) $row->addon_name;
            $addonKey = $addonId !== null ? (string) $addonId : 'legacy:' . $addonName;
            $salesByAddon[$addonKey]['key'] = $addonKey;
            $salesByAddon[$addonKey]['add_on_id'] = $addonId;
            $salesByAddon[$addonKey]['addon_name'] = $addonName;
            $salesByAddon[$addonKey]['daily'][(string) $row->sold_on] = (float) $row->quantity_sold;
        }

        $forecastDates = collect(range(1, $forecastDays))
            ->map(fn (int $offset) => $today->copy()->addDays($offset));
        $forecasts = [];
        $dailyItems = [];
        $dailyAddons = [];

        foreach ($forecastDates as $date) {
            $matchingHistoryDates = $historyDates
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

            foreach ($salesByAddon as $sales) {
                $total = 0.0;
                foreach ($matchingHistoryDates as $historyDate) {
                    $total += (float) ($sales['daily'][$historyDate] ?? 0);
                }
                $quantity = $sampleCount > 0 ? round($total / $sampleCount, 2) : 0.0;
                if ($quantity > 0) {
                    $dailyAddons[$sales['key']]['key'] = $sales['key'];
                    $dailyAddons[$sales['key']]['add_on_id'] = $sales['add_on_id'];
                    $dailyAddons[$sales['key']]['addon_name'] = $sales['addon_name'];
                    $dailyAddons[$sales['key']]['forecast_quantity'] =
                        ($dailyAddons[$sales['key']]['forecast_quantity'] ?? 0) + $quantity;
                }
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
            'peak_hours' => $this->getPeakHours($today, $chartDays),
            'hourly_top_recipes' => $this->getHourlyTopRecipes($today),
            'forecast_comparison' => $this->getForecastComparison($historyRows, $today, $chartDays),
            'ingredient_recommendations' => $this->getIngredientRecommendations($dailyItems, $dailyAddons),
        ];
    }

    private function getPeakHours(Carbon $today, int $chartDays): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpression = $this->businessHourExpression($driver);
        $start = $today->copy()->subDays($chartDays)->startOfDay();
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
            ->whereBetween('orders.created_at', [$start->utc(), $end->utc()])
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

    private function getHourlyTopRecipes(Carbon $today): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpression = $this->businessHourExpression($driver);
        $start = $today->copy()->subDays(self::RECENT_DAYS)->startOfDay();
        $end = $today->copy()->subDay()->endOfDay();

        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', [Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY, Order::STATUS_SERVED, Order::STATUS_COMPLETED])
            ->whereBetween('orders.created_at', [$start->copy()->utc(), $end->copy()->utc()])
            ->whereNotNull('order_items.menu_item_id')
            ->selectRaw("{$hourExpression} AS sales_hour")
            ->addSelect('order_items.menu_item_id', 'order_items.menu_item_variant_id')
            ->selectRaw('MAX(order_items.item_name) AS item_name')
            ->selectRaw('SUM(order_items.quantity) AS quantity_sold')
            ->groupBy('sales_hour', 'order_items.menu_item_id', 'order_items.menu_item_variant_id')
            ->get();

        $byHour = [];
        foreach ($rows as $row) {
            $hour = (int) $row->sales_hour;
            $key = $this->targetKey((int) $row->menu_item_id, $row->menu_item_variant_id !== null ? (int) $row->menu_item_variant_id : null);
            $byHour[$hour]['total'] = ($byHour[$hour]['total'] ?? 0) + (float) $row->quantity_sold;
            $byHour[$hour]['items'][] = [
                'key' => $key,
                'menu_item_id' => (int) $row->menu_item_id,
                'variant_id' => $row->menu_item_variant_id !== null ? (int) $row->menu_item_variant_id : null,
                'item_name' => (string) $row->item_name,
                'quantity_sold' => (float) $row->quantity_sold,
            ];
        }

        $mappings = RecipeMapping::query()
            ->with('rawMaterial')
            ->whereNotNull('menu_item_id')
            ->whereNull('add_on_id')
            ->get()
            ->groupBy(fn (RecipeMapping $mapping) => $this->targetKey(
                (int) $mapping->menu_item_id,
                $mapping->variant_id !== null ? (int) $mapping->variant_id : null
            ));

        $result = [];
        foreach ($byHour as $hour => $sales) {
            usort($sales['items'], fn (array $a, array $b) => $b['quantity_sold'] <=> $a['quantity_sold']);
            $top = $sales['items'][0];
            $recipe = $mappings->get($top['key']);
            if (!$recipe || $recipe->isEmpty()) {
                $recipe = $mappings->get($this->targetKey($top['menu_item_id'], null));
            }
            $ingredients = [];
            foreach ($recipe ?? [] as $mapping) {
                $material = $mapping->rawMaterial;
                if (!$material || !$material->is_active) continue;
                $ingredients[] = [
                    'raw_material_id' => (int) $material->id,
                    'material_name' => (string) $material->material_name,
                    'unit' => (string) $material->base_unit,
                    'quantity_required' => round((float) $mapping->quantity * $top['quantity_sold'], 4),
                    'unit_mismatch' => strtolower(trim((string) $material->base_unit)) !== strtolower(trim((string) $mapping->unit)),
                ];
            }
            $result[] = [
                'hour' => (int) $hour,
                'hour_total' => round((float) $sales['total'], 2),
                'item_name' => $top['item_name'],
                'menu_item_id' => $top['menu_item_id'],
                'variant_id' => $top['variant_id'],
                'quantity_sold' => round((float) $top['quantity_sold'], 2),
                'sales_share_percent' => $sales['total'] > 0 ? round($top['quantity_sold'] / $sales['total'] * 100, 1) : 0,
                'ingredients' => $ingredients,
            ];
        }

        usort($result, fn (array $a, array $b) => $b['hour_total'] <=> $a['hour_total']);
        return array_slice($result, 0, 6);
    }

    private function getForecastComparison($historyRows, Carbon $today, int $comparisonDays): array
    {
        $salesByTarget = [];
        foreach ($historyRows as $row) {
            if ($row->menu_item_id === null) continue;
            $menuItemId = (int) $row->menu_item_id;
            $variantId = $row->menu_item_variant_id !== null ? (int) $row->menu_item_variant_id : null;
            $key = $this->targetKey($menuItemId, $variantId);
            $salesByTarget[$key]['menu_item_id'] = $menuItemId;
            $salesByTarget[$key]['variant_id'] = $variantId;
            $salesByTarget[$key]['item_name'] = (string) $row->item_name;
            $salesByTarget[$key]['daily'][(string) $row->sold_on] = (float) $row->quantity_sold;
        }

        $comparisonStart = $today->copy()->subDays($comparisonDays);
        $comparisonEnd = $today->copy()->subDay();
        $comparisonRows = [];
        $dailyComparison = [];
        for ($date = $comparisonStart->copy(); $date->lte($comparisonEnd); $date->addDay()) {
            $dailyComparison[$date->toDateString()] = [
                'date' => $date->toDateString(),
                'forecast_quantity' => 0.0,
                'actual_quantity' => 0.0,
            ];
        }

        foreach ($salesByTarget as $key => $sales) {
            $forecastQuantity = 0.0;
            $actualQuantity = 0.0;

            for ($targetDate = $comparisonStart->copy(); $targetDate->lte($comparisonEnd); $targetDate->addDay()) {
                $actualDate = $targetDate->toDateString();
                $dailyActual = (float) ($sales['daily'][$actualDate] ?? 0);
                $actualQuantity += $dailyActual;

                $lookbackStart = $targetDate->copy()->subDays(self::HISTORY_DAYS);
                $lookbackTotal = 0.0;
                $matchingDays = 0;
                for ($historyDate = $lookbackStart->copy(); $historyDate->lt($targetDate); $historyDate->addDay()) {
                    if ($historyDate->dayOfWeek !== $targetDate->dayOfWeek) continue;
                    $matchingDays++;
                    $lookbackTotal += (float) ($sales['daily'][$historyDate->toDateString()] ?? 0);
                }
                $dailyForecast = $matchingDays > 0 ? round($lookbackTotal / $matchingDays, 2) : 0.0;
                $forecastQuantity += $dailyForecast;
                $dailyComparison[$actualDate]['forecast_quantity'] += $dailyForecast;
                $dailyComparison[$actualDate]['actual_quantity'] += $dailyActual;
            }

            if ($forecastQuantity <= 0 && $actualQuantity <= 0) continue;
            $comparisonRows[$key] = [
                'menu_item_id' => $sales['menu_item_id'],
                'variant_id' => $sales['variant_id'],
                'item_name' => $sales['item_name'],
                'forecast_quantity' => round($forecastQuantity, 2),
                'actual_quantity' => round($actualQuantity, 2),
            ];
        }

        $forecastTotal = array_sum(array_column($comparisonRows, 'forecast_quantity'));
        $actualTotal = array_sum(array_column($comparisonRows, 'actual_quantity'));
        $scale = max($forecastTotal, $actualTotal);
        $absoluteError = array_sum(array_map(
            fn (array $row) => abs($row['actual_quantity'] - $row['forecast_quantity']),
            $comparisonRows
        ));
        $accuracy = $scale > 0 ? max(0, 100 * (1 - $absoluteError / $scale)) : null;
        $items = collect($comparisonRows)
            ->map(function (array $row): array {
                $row['difference'] = round($row['actual_quantity'] - $row['forecast_quantity'], 2);
                $scale = max($row['forecast_quantity'], $row['actual_quantity']);
                $row['accuracy_percent'] = $scale > 0
                    ? round(max(0, 100 * (1 - abs($row['actual_quantity'] - $row['forecast_quantity']) / $scale)), 1)
                    : null;
                return $row;
            })
            ->sortByDesc('actual_quantity')
            ->values()
            ->all();

        return [
            'from' => $comparisonStart->toDateString(),
            'to' => $comparisonEnd->toDateString(),
            'days_compared' => $comparisonDays,
            'forecast_quantity' => round($forecastTotal, 2),
            'actual_quantity' => round($actualTotal, 2),
            'accuracy_percent' => round($accuracy, 1),
            'daily' => array_values(array_map(fn (array $row) => [
                ...$row,
                'forecast_quantity' => round($row['forecast_quantity'], 2),
                'actual_quantity' => round($row['actual_quantity'], 2),
            ], $dailyComparison)),
            'items' => $items,
        ];
    }

    private function getIngredientRecommendations(array $dailyItems, array $dailyAddons): array
    {
        if ($dailyItems === [] && $dailyAddons === []) {
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
        $addonMappings = RecipeMapping::query()
            ->with(['rawMaterial.restaurantStock'])
            ->whereNotNull('add_on_id')
            ->get()
            ->groupBy(fn (RecipeMapping $mapping) => (int) $mapping->add_on_id);

        $ingredientNeeds = [];
        $unmappedItems = [];
        $unmappedAddons = [];

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

        foreach ($dailyAddons as $addon) {
            $recipe = $addonMappings->get((int) $addon['add_on_id']);
            if (!$recipe || $recipe->isEmpty()) {
                $unmappedAddons[$addon['key']] = [
                    'add_on_id' => $addon['add_on_id'],
                    'addon_name' => $addon['addon_name'],
                ];
                continue;
            }
            foreach ($recipe as $mapping) {
                $material = $mapping->rawMaterial;
                if (!$material || !$material->is_active) continue;
                $baseUnit = strtolower(trim((string) $material->base_unit));
                $recipeUnit = strtolower(trim((string) $mapping->unit));
                $id = (int) $material->id;
                $ingredientNeeds[$id]['raw_material_id'] = $id;
                $ingredientNeeds[$id]['material_name'] = (string) $material->material_name;
                $ingredientNeeds[$id]['unit'] = (string) $material->base_unit;
                $ingredientNeeds[$id]['forecast_usage'] = ($ingredientNeeds[$id]['forecast_usage'] ?? 0)
                    + ((float) $mapping->quantity * (float) $addon['forecast_quantity']);
                $ingredientNeeds[$id]['unit_mismatch'] = ($ingredientNeeds[$id]['unit_mismatch'] ?? false)
                    || $baseUnit === '' || $baseUnit !== $recipeUnit;
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
            'unmapped_addons' => array_values($unmappedAddons),
        ];
    }

    private function businessDateExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "DATE(orders.created_at, '+6 hours')",
            'pgsql' => "DATE(orders.created_at + INTERVAL '6 hours')",
            default => 'DATE(DATE_ADD(orders.created_at, INTERVAL 6 HOUR))',
        };
    }

    private function businessHourExpression(string $driver): string
    {
        return match ($driver) {
            'sqlite' => "CAST(strftime('%H', orders.created_at, '+6 hours') AS INTEGER)",
            'pgsql' => "CAST(EXTRACT(HOUR FROM orders.created_at + INTERVAL '6 hours') AS INTEGER)",
            default => 'HOUR(DATE_ADD(orders.created_at, INTERVAL 6 HOUR))',
        };
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
