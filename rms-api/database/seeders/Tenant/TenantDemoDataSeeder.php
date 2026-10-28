<?php

namespace Database\Seeders\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class TenantDemoDataSeeder extends Seeder
{
    private const PREFIX = 'DEMO100-';

    public function run(int $count = 100): array
    {
        if ($count !== 100) {
            throw new RuntimeException('This demo seeder is intentionally fixed at 100 records per module.');
        }

        $db = DB::connection('tenant');

        if ($db->table('orders')->where('order_number', 'like', self::PREFIX.'ORD-%')->exists()) {
            throw new RuntimeException('Demo data is already seeded for this tenant. Existing data was left unchanged.');
        }

        if ($db->table('users')->where('username', 'like', 'demo100-staff-%')->exists()) {
            throw new RuntimeException('DEMO100 staff accounts already exist; refusing to duplicate the demo seed.');
        }

        return $db->transaction(function () use ($db, $count): array {
            $now = now();
            $adminId = $db->table('users')->orderBy('id')->value('id');
            $this->requireTables($db, [
                'users', 'roles', 'employees', 'attendances', 'restaurant_tables', 'reservations',
                'reservation_tables', 'customers', 'menu_categories', 'menu_items', 'raw_materials',
                'warehouse_stocks', 'restaurant_stocks', 'suppliers', 'purchase_orders',
                'purchase_order_items', 'orders', 'order_kitchen_batches', 'order_items',
                'order_payments', 'expenses', 'expense_categories', 'salary_payrolls', 'salary_details',
            ]);

            $roleIds = $db->table('roles')->pluck('id', 'name');
            $staffRoleId = $roleIds['waiter'] ?? $roleIds['chef'] ?? $roleIds['manager'] ?? $roleIds['admin'] ?? null;
            if (!$staffRoleId) {
                throw new RuntimeException('No tenant role exists for demo staff accounts.');
            }

            $staff = [];
            $employees = [];
            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $userId = $db->table('users')->insertGetId([
                    'role_id' => $i === 1 ? ($roleIds['chef'] ?? $staffRoleId) : $staffRoleId,
                    'name' => "Demo Staff {$n}",
                    'username' => "demo100-staff-{$n}",
                    'email' => "demo100-staff-{$n}@local.test",
                    'password' => Hash::make($i === 1 ? 'DemoChef123!' : Str::random(48)),
                    'is_active' => true,
                    'must_change_password' => $i !== 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $staff[] = $userId;
                $employees[] = $db->table('employees')->insertGetId([
                    'user_id' => $userId,
                    'phone' => '880180100'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'joining_date' => $now->copy()->subDays($i + 30)->toDateString(),
                    'hourly_rate' => 120 + ($i % 8) * 10,
                    'current_status' => 'none',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $customers = [];
            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $customers[] = $db->table('customers')->insertGetId([
                    'name' => "Demo Customer {$n}",
                    'phone' => '880190100'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'email' => "demo-customer-{$n}@local.test",
                    'total_orders' => 0,
                    'total_spent' => 0,
                    'is_active' => true,
                    'notes' => 'DEMO DATA: generated for local module preview.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $tables = [];
            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $tables[] = $db->table('restaurant_tables')->insertGetId([
                    'table_name' => self::PREFIX."TABLE-{$n}",
                    'capacity' => 2 + ($i % 7),
                    'section' => ['ac', 'non_ac', 'outdoor'][$i % 3],
                    'status' => 'available',
                    'notes' => 'DEMO DATA',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $menuCategories = [
                'Burgers' => 'Grilled and crispy chicken and beef burgers.',
                'Pizza' => 'Freshly baked pizzas with classic toppings.',
                'Rice and Biryani' => 'Aromatic rice dishes and traditional biryani.',
                'Fast Food' => 'Crispy snacks and quick bites.',
                'Combos' => 'Complete meal combos with sides and drinks.',
                'Set Meals' => 'Freshly prepared complete meals.',
                'Beverages' => 'Chilled drinks, fresh juice and coffee.',
                'Desserts' => 'Freshly prepared sweets and frozen desserts.',
            ];
            $categoryIdsByName = $db->table('menu_categories')
                ->whereNull('deleted_at')
                ->pluck('id', 'category_name')
                ->all();
            $categoryOrder = count($categoryIdsByName) + 1;
            foreach ($menuCategories as $categoryName => $description) {
                if (isset($categoryIdsByName[$categoryName])) {
                    continue;
                }

                $categoryIdsByName[$categoryName] = $db->table('menu_categories')->insertGetId([
                    'category_name' => $categoryName,
                    'description' => $description,
                    'is_available' => true,
                    'display_order' => $categoryOrder++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $menuCatalog = $this->menuCatalog();

            $priceMultipliers = [0.8, 1, 1.4, 1.8, 1.2];
            $menuItems = [];
            for ($i = 0; $i < $count; $i++) {
                $productIndex = $i % count($menuCatalog);
                $variantIndex = intdiv($i, count($menuCatalog));
                $product = $menuCatalog[$productIndex];
                $itemName = $product['name'].' - '.$product['variants'][$variantIndex];
                $price = round(($product['price'] * $priceMultipliers[$variantIndex]) / 10) * 10;
                $categoryId = $categoryIdsByName[$product['category']] ?? reset($categoryIdsByName);
                $menuItemId = $db->table('menu_items')->insertGetId([
                    'menu_category_id' => $categoryId,
                    'menu_name' => $itemName,
                    'item_type' => $product['type'],
                    'price' => $price,
                    'ingredients' => $product['ingredients'],
                    'description' => $product['description'],
                    'image_path' => $product['image'],
                    'preparation_time' => $product['minutes'],
                    'is_available' => true,
                    'is_featured' => $product['featured'] && $variantIndex === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $menuItems[] = ['id' => $menuItemId, 'name' => $itemName, 'price' => $price];
            }

            $suppliers = [];
            $materials = [];
            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $suppliers[] = $db->table('suppliers')->insertGetId([
                    'supplier_name' => self::PREFIX."Supplier {$n}",
                    'contact_person' => "Contact {$n}",
                    'email' => "demo-supplier-{$n}@local.test",
                    'phone' => '880170100'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'address' => 'Local demo address',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $materials[] = $db->table('raw_materials')->insertGetId([
                    'material_name' => self::PREFIX."Raw Material {$n}",
                    'category' => ['Produce', 'Dairy', 'Dry Goods', 'Meat'][$i % 4],
                    'base_unit' => ['kg', 'litre', 'piece'][$i % 3],
                    'warehouse_minimum_quantity' => 5,
                    'restaurant_minimum_quantity' => 2,
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($materials as $i => $materialId) {
                $quantity = 30 + ($i % 70);
                $stock = [
                    'raw_material_id' => $materialId,
                    'quantity' => $quantity,
                    'average_unit_cost' => 12 + ($i % 20),
                    'last_received_at' => $now,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $db->table('warehouse_stocks')->insert($stock);
                $stock['quantity'] = 4 + ($i % 25);
                $db->table('restaurant_stocks')->insert($stock);
            }

            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $start = Carbon::today()->addDays(($i - 1) % 30)->setTime(12 + ($i % 8), ($i % 2) * 30);
                $reservationId = $db->table('reservations')->insertGetId([
                    'reservation_no' => self::PREFIX."RES-{$n}",
                    'customer_id' => $customers[$i - 1],
                    'reservation_date' => $start->toDateString(),
                    'start_time' => $start,
                    'end_time' => $start->copy()->addHours(2),
                    'guest_count' => 2 + ($i % 6),
                    'advance_amount' => $i % 3 === 0 ? 500 : 0,
                    'status' => $i % 7 === 0 ? 'confirmed' : 'reserved',
                    'note' => 'DEMO DATA: test reservation.',
                    'created_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $db->table('reservation_tables')->insert([
                    'reservation_id' => $reservationId,
                    'restaurant_table_id' => $tables[$i - 1],
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $expenseCategoryId = $db->table('expense_categories')->orderBy('id')->value('id');
            if (!$expenseCategoryId) {
                $expenseCategoryId = $db->table('expense_categories')->insertGetId([
                    'name' => self::PREFIX.'General Expense',
                    'description' => 'Local demo category',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $attendanceIds = [];
            $payrollIds = [];
            for ($i = 1; $i <= $count; $i++) {
                $day = Carbon::today()->subDays($i % 30);
                $start = $day->copy()->setTime(9, 0);
                $end = $day->copy()->setTime(17, 0);
                $attendanceIds[] = $db->table('attendances')->insertGetId([
                    'employee_id' => $employees[$i - 1],
                    'attendance_date' => $day->toDateString(),
                    'scheduled_start_at' => $start,
                    'scheduled_end_at' => $end,
                    'grace_minutes' => 10,
                    'scheduled_minutes' => 480,
                    'check_in_at' => $start->copy()->addMinutes($i % 16),
                    'check_out_at' => $end,
                    'status' => 'completed',
                    'late_minutes' => max(0, ($i % 16) - 10),
                    'worked_minutes' => 480 - ($i % 16),
                    'break_minutes' => 30,
                    'overtime_minutes' => $i % 5 === 0 ? 30 : 0,
                    'early_leave_minutes' => 0,
                    'is_manual' => false,
                    'notes' => 'DEMO DATA',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $rate = 120 + ($i % 8) * 10;
                $pay = $rate * 8;
                $payrollIds[] = $db->table('salary_payrolls')->insertGetId([
                    'employee_id' => $employees[$i - 1],
                    'employee_name' => "Demo Staff ".str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'employee_phone' => '880180100'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'employee_email' => "demo-staff-".str_pad((string) $i, 3, '0', STR_PAD_LEFT).'@local.test',
                    'period_start' => $day->copy()->startOfMonth()->toDateString(),
                    'period_end' => $day->copy()->endOfMonth()->toDateString(),
                    'hourly_rate' => $rate,
                    'regular_salary' => $pay,
                    'overtime_salary' => 0,
                    'adjustment_amount' => 0,
                    'total_amount' => $pay,
                    'payment_status' => 'unpaid',
                    'notes' => 'DEMO DATA payroll',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $db->table('salary_details')->insert([
                    'salary_payroll_id' => $payrollIds[$i - 1],
                    'attendance_id' => $attendanceIds[$i - 1],
                    'employee_id' => $employees[$i - 1],
                    'salary_date' => Carbon::today()->subDays($i % 30)->toDateString(),
                    'scheduled_minutes' => 480,
                    'worked_minutes' => 480 - ($i % 16),
                    'late_minutes' => max(0, ($i % 16) - 10),
                    'break_minutes' => 30,
                    'overtime_minutes' => $i % 5 === 0 ? 30 : 0,
                    'hourly_rate' => 120 + ($i % 8) * 10,
                    'salary_type' => 'hourly',
                    'regular_salary' => (120 + ($i % 8) * 10) * 8,
                    'overtime_salary' => 0,
                    'total_amount' => (120 + ($i % 8) * 10) * 8,
                    'calculation_source' => 'attendance',
                    'notes' => "DEMO DATA salary detail {$n}",
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $createdOrders = [];
            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $orderCreatedAt = $now->copy()->subMinutes(($i - 1) % 35);
                $status = $i <= 6 ? 'preparing' : ($i <= 12 ? 'ready' : 'completed');
                $isDone = $status === 'completed';
                $item = $menuItems[$i - 1];
                $amount = $item['price'];
                $paid = $amount;
                $orderId = $db->table('orders')->insertGetId([
                    'order_number' => self::PREFIX."ORD-{$n}",
                    'customer_id' => $customers[$i - 1],
                    'customer_name' => "Demo Customer {$n}",
                    'customer_phone' => '880190100'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'fulfillment_type' => 'takeaway',
                    'pickup_token' => null,
                    'pickup_status' => $isDone ? 'collected' : 'uncollected',
                    'picked_up_at' => $isDone ? $orderCreatedAt->copy()->addMinutes(30) : null,
                    'status' => $status,
                    'subtotal' => $amount,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'service_charge' => 0,
                    'total_amount' => $amount,
                    'paid_amount' => $paid,
                    'due_amount' => $amount - $paid,
                    'payment_status' => $paid >= $amount ? 'paid' : ($paid > 0 ? 'partially_paid' : 'due'),
                    'payment_method' => $paid > 0 ? 'cash' : null,
                    'order_note' => 'DEMO DATA order for local module preview.',
                    'kitchen_note' => 'Demo kitchen note.',
                    'sent_to_kitchen_at' => $status === 'pending' ? null : $orderCreatedAt,
                    'preparing_at' => in_array($status, ['preparing', 'ready', 'completed'], true) ? $orderCreatedAt : null,
                    'ready_at' => in_array($status, ['ready', 'completed'], true) ? $orderCreatedAt->copy()->addMinutes(12) : null,
                    'served_at' => $isDone ? $orderCreatedAt->copy()->addMinutes(30) : null,
                    'completed_at' => $isDone ? $orderCreatedAt->copy()->addMinutes(30) : null,
                    'is_customer_spend_recorded' => $isDone,
                    'customer_spend_recorded_at' => $isDone ? $orderCreatedAt->copy()->addMinutes(30) : null,
                    'created_by' => $staff[($i - 1) % count($staff)],
                    'created_at' => $orderCreatedAt,
                    'updated_at' => $now,
                ]);
                $db->table('orders')->where('id', $orderId)->update([
                    'pickup_token' => 'T-'.str_pad((string) $orderId, 5, '0', STR_PAD_LEFT),
                ]);
                $batchStatus = match ($status) {
                    'pending' => 'pending',
                    'preparing' => 'preparing',
                    default => 'ready',
                };
                $batchId = $db->table('order_kitchen_batches')->insertGetId([
                    'order_id' => $orderId,
                    'batch_no' => 1,
                    'status' => $isDone ? 'served' : $batchStatus,
                    'chef_id' => $staff[0],
                    'sent_to_kitchen_at' => $status === 'pending' ? null : $orderCreatedAt,
                    'preparing_at' => in_array($status, ['preparing', 'ready', 'completed'], true) ? $orderCreatedAt : null,
                    'ready_at' => in_array($status, ['ready', 'completed'], true) ? $orderCreatedAt->copy()->addMinutes(12) : null,
                    'served_at' => $isDone ? $orderCreatedAt->copy()->addMinutes(30) : null,
                    'created_by' => $staff[($i - 1) % count($staff)],
                    'created_at' => $orderCreatedAt,
                    'updated_at' => $now,
                ]);
                $db->table('order_items')->insert([
                    'order_id' => $orderId,
                    'order_kitchen_batch_id' => $batchId,
                    'menu_item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'unit_price' => $amount,
                    'quantity' => 1,
                    'addon_total' => 0,
                    'line_total' => $amount,
                    'status' => $isDone ? 'served' : $status,
                    'kitchen_note' => 'DEMO DATA',
                    'created_at' => $orderCreatedAt,
                    'updated_at' => $now,
                ]);
                $db->table('order_payments')->insert([
                    'order_id' => $orderId,
                    'amount' => $paid,
                    'payment_method' => 'cash',
                    'reference' => self::PREFIX."PAY-{$n}",
                    'note' => 'DEMO DATA payment',
                    'received_by' => $staff[($i - 1) % count($staff)],
                    'created_at' => $orderCreatedAt,
                    'updated_at' => $now,
                ]);
                $createdOrders[] = $orderId;
                if ($isDone) {
                    $db->table('customers')->where('id', $customers[$i - 1])->update([
                        'total_orders' => 1,
                        'total_spent' => $amount,
                        'last_visit_at' => $orderCreatedAt->copy()->addMinutes(30),
                    ]);
                }
            }

            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $amount = 250 + ($i % 20) * 50;
                $db->table('expenses')->insert([
                    'expense_category_id' => $expenseCategoryId,
                    'expense_date' => $now->copy()->subDays($i % 60),
                    'amount' => $amount,
                    'payment_method' => ['cash', 'bank_transfer', 'card'][$i % 3],
                    'notes' => self::PREFIX."Expense {$n}",
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $purchaseOrderId = $db->table('purchase_orders')->insertGetId([
                    'supplier_id' => $suppliers[$i - 1],
                    'order_date' => $now->copy()->subDays($i % 45),
                    'delivery_date' => $now->copy()->addDays(($i % 10) + 1)->toDateString(),
                    'status' => 'ordered',
                    'subtotal' => $amount,
                    'tax' => 0,
                    'service_charge' => 0,
                    'total_amount' => $amount,
                    'paid_amount' => 0,
                    'due_amount' => $amount,
                    'payment_method' => null,
                    'ordered_by' => $staff[($i - 1) % count($staff)],
                    'notes' => self::PREFIX."Purchase Order {$n}",
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $db->table('purchase_order_items')->insert([
                    'purchase_order_id' => $purchaseOrderId,
                    'raw_material_id' => $materials[$i - 1],
                    'item_name' => self::PREFIX."Raw Material {$n}",
                    'unit' => ['kg', 'litre', 'piece'][$i % 3],
                    'quantity' => 10 + ($i % 20),
                    'received_quantity' => 0,
                    'unit_price' => $amount / (10 + ($i % 20)),
                    'total_price' => $amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return [
                'users' => $count,
                'staff' => $count,
                'customers' => $count,
                'tables' => $count,
                'menu_items' => $count,
                'suppliers' => $count,
                'raw_materials' => $count,
                'warehouse_stocks' => $count,
                'restaurant_stocks' => $count,
                'reservations' => $count,
                'orders' => $count,
                'order_items' => $count,
                'kitchen_batches' => $count,
                'billing_payments' => $count,
                'attendances' => $count,
                'salary_payrolls' => $count,
                'salary_details' => $count,
                'expenses' => $count,
                'purchase_orders' => $count,
                'purchase_order_items' => $count,
            ];
        });
    }

    public function refreshExistingMenuItems(): int
    {
        $db = DB::connection('tenant');
        $existingItems = $db->table('menu_items')
            ->whereNull('deleted_at')
            ->where('menu_name', 'like', self::PREFIX.'Menu Item %')
            ->orderBy('id')
            ->get(['id']);

        if ($existingItems->isEmpty()) {
            return 0;
        }

        $menuCatalog = $this->menuCatalog();
        $menuCategories = [
            'Burgers' => 'Grilled and crispy chicken and beef burgers.',
            'Pizza' => 'Freshly baked pizzas with classic toppings.',
            'Rice and Biryani' => 'Aromatic rice dishes and traditional biryani.',
            'Fast Food' => 'Crispy snacks and quick bites.',
            'Combos' => 'Complete meal combos with sides and drinks.',
            'Set Meals' => 'Freshly prepared complete meals.',
            'Beverages' => 'Chilled drinks, fresh juice and coffee.',
            'Desserts' => 'Freshly prepared sweets and frozen desserts.',
        ];
        $categoryIdsByName = $db->table('menu_categories')
            ->whereNull('deleted_at')
            ->pluck('id', 'category_name')
            ->all();
        $categoryOrder = count($categoryIdsByName) + 1;

        foreach ($menuCategories as $categoryName => $description) {
            if (isset($categoryIdsByName[$categoryName])) {
                continue;
            }

            $categoryIdsByName[$categoryName] = $db->table('menu_categories')->insertGetId([
                'category_name' => $categoryName,
                'description' => $description,
                'is_available' => true,
                'display_order' => $categoryOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $priceMultipliers = [0.8, 1, 1.4, 1.8, 1.2];

        return $db->transaction(function () use ($db, $existingItems, $menuCatalog, $priceMultipliers, $categoryIdsByName): int {
            foreach ($existingItems as $index => $existingItem) {
                $productIndex = $index % count($menuCatalog);
                $variantIndex = intdiv($index, count($menuCatalog)) % 5;
                $product = $menuCatalog[$productIndex];
                $itemName = $product['name'].' - '.$product['variants'][$variantIndex];
                $price = round(($product['price'] * $priceMultipliers[$variantIndex]) / 10) * 10;

                $db->table('menu_items')->where('id', $existingItem->id)->update([
                    'menu_category_id' => $categoryIdsByName[$product['category']],
                    'menu_name' => $itemName,
                    'item_type' => $product['type'],
                    'price' => $price,
                    'ingredients' => $product['ingredients'],
                    'description' => $product['description'],
                    'image_path' => $product['image'],
                    'preparation_time' => $product['minutes'],
                    'is_available' => true,
                    'is_featured' => $product['featured'] && $variantIndex === 0,
                    'updated_at' => now(),
                ]);

                $db->table('order_items')->where('menu_item_id', $existingItem->id)->update([
                    'item_name' => $itemName,
                ]);
            }

            return $existingItems->count();
        });
    }

    private function menuCatalog(): array
    {
        return [
            ['category' => 'Burgers', 'name' => 'Classic Chicken Burger', 'type' => 'regular', 'price' => 250, 'ingredients' => 'Chicken patty, lettuce, tomato, onion, mayonnaise and burger bun.', 'description' => 'Juicy grilled chicken with crisp lettuce and house mayonnaise.', 'minutes' => 15, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1692737348416-0a4fa4cf1523?auto=format&fit=crop&w=480&q=80', 'variants' => ['Single Patty', 'Double Patty', 'Spicy Chicken', 'Cheese Melt', 'BBQ Chicken']],
            ['category' => 'Burgers', 'name' => 'Beef Cheese Burger', 'type' => 'regular', 'price' => 320, 'ingredients' => 'Beef patty, cheddar cheese, lettuce, onion, pickles and toasted bun.', 'description' => 'Grilled beef patty with melted cheese, pickles and house sauce.', 'minutes' => 18, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1598182198871-d3f4ab4fd181?auto=format&fit=crop&w=480&q=80', 'variants' => ['Classic', 'Double Beef', 'Mushroom Swiss', 'BBQ Beef', 'Jalapeño Cheese']],
            ['category' => 'Burgers', 'name' => 'Crispy Chicken Burger', 'type' => 'regular', 'price' => 280, 'ingredients' => 'Crispy chicken fillet, lettuce, mayonnaise, cheese and burger bun.', 'description' => 'Golden crispy chicken fillet with fresh lettuce and creamy sauce.', 'minutes' => 17, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1692737348416-0a4fa4cf1523?auto=format&fit=crop&w=480&q=80', 'variants' => ['Classic Fillet', 'Spicy Fillet', 'Double Fillet', 'Cheese Fillet', 'Hot Crispy']],
            ['category' => 'Pizza', 'name' => 'Chicken Cheese Pizza', 'type' => 'regular', 'price' => 450, 'ingredients' => 'Pizza dough, chicken, mozzarella, capsicum, onion and tomato sauce.', 'description' => 'Oven-baked pizza topped with seasoned chicken and mozzarella.', 'minutes' => 25, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1604917869287-3ae73c77e227?auto=format&fit=crop&w=480&q=80', 'variants' => ['Personal', 'Small', 'Medium', 'Large', 'Extra Cheese']],
            ['category' => 'Pizza', 'name' => 'Beef Pepperoni Pizza', 'type' => 'regular', 'price' => 520, 'ingredients' => 'Pizza dough, beef pepperoni, mozzarella, olives and pizza sauce.', 'description' => 'Classic beef pepperoni pizza with bubbling mozzarella cheese.', 'minutes' => 27, 'featured' => true, 'image' => 'https://tasty.athinorama.gr/Content/ImagesDatabase/p/696x522/crop/both/6c/6c88bc7af43b4dc4b4713fb0432941fb.jpg', 'variants' => ['Personal', 'Small', 'Medium', 'Large', 'Extra Pepperoni']],
            ['category' => 'Rice and Biryani', 'name' => 'Chicken Biryani', 'type' => 'regular', 'price' => 280, 'ingredients' => 'Basmati rice, chicken, potato, fried onion and aromatic spices.', 'description' => 'Fragrant basmati rice layered with spiced chicken and fried onions.', 'minutes' => 20, 'featured' => true, 'image' => 'https://media-assets.swiggy.com/swiggy/image/upload/fl_lossy%2Cf_auto%2Cq_auto%2Cw_400/RX_THUMBNAIL/IMAGES/VENDOR/2026/2/20/07926d90-610f-40f9-9944-304274e9fcf5_533361.jpg', 'variants' => ['Half Plate', 'Full Plate', 'Extra Chicken', 'Family Pack', 'Spicy Biryani']],
            ['category' => 'Rice and Biryani', 'name' => 'Beef Tehari', 'type' => 'regular', 'price' => 320, 'ingredients' => 'Aromatic rice, tender beef, mustard oil, green chilli and spices.', 'description' => 'Dhakai-style beef tehari cooked with mustard oil and fragrant spices.', 'minutes' => 20, 'featured' => true, 'image' => 'https://recipes.milon.im/assets/images/recipes/beef-tehari.jpg', 'variants' => ['Half Plate', 'Full Plate', 'Extra Beef', 'Family Pack', 'Spicy Tehari']],
            ['category' => 'Rice and Biryani', 'name' => 'Family Rice Set', 'type' => 'set_meal', 'price' => 1250, 'ingredients' => 'Fried rice, chicken curry, mixed vegetables and fresh salad.', 'description' => 'A generous rice and chicken meal made for sharing.', 'minutes' => 35, 'featured' => true, 'image' => 'https://prcdn.freetls.fastly.net/release_image/167099/7/167099-7-e0574fd57dd7af399c8fd6ae6e9f44d42-733x572.jpg?auto=webp&fit=bounds&w=480', 'variants' => ['For Two', 'For Four', 'For Six', 'Party Tray', 'Extra Chicken']],
            ['category' => 'Fast Food', 'name' => 'French Fries', 'type' => 'regular', 'price' => 150, 'ingredients' => 'Potato, salt and seasoning.', 'description' => 'Golden potato fries with a crisp outside and fluffy centre.', 'minutes' => 10, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1688978181542-87a886a16fbe?auto=format&fit=crop&w=480&q=80', 'variants' => ['Regular', 'Large', 'Family Size', 'Peri Peri', 'Cheese Fries']],
            ['category' => 'Fast Food', 'name' => 'Chicken Wings', 'type' => 'regular', 'price' => 300, 'ingredients' => 'Chicken wings, spices and special sauce.', 'description' => 'Crispy chicken wings tossed in a savoury house sauce.', 'minutes' => 18, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1624153064067-566cae78993d?auto=format&fit=crop&w=480&q=80', 'variants' => ['6 Pieces', '12 Pieces', '18 Pieces', 'BBQ Wings', 'Spicy Wings']],
            ['category' => 'Combos', 'name' => 'Burger Combo', 'type' => 'combo', 'price' => 420, 'ingredients' => 'Chicken burger, French fries and soft drink.', 'description' => 'A chicken burger served with fries and a chilled drink.', 'minutes' => 20, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1692737348416-0a4fa4cf1523?auto=format&fit=crop&w=480&q=80', 'variants' => ['Chicken Burger', 'Beef Burger', 'Double Burger', 'Spicy Burger', 'Family Combo']],
            ['category' => 'Combos', 'name' => 'Pizza Combo', 'type' => 'combo', 'price' => 750, 'ingredients' => 'Chicken pizza, French fries and two soft drinks.', 'description' => 'A freshly baked chicken pizza with fries and drinks.', 'minutes' => 30, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1604917869287-3ae73c77e227?auto=format&fit=crop&w=480&q=80', 'variants' => ['Personal Combo', 'Small Combo', 'Medium Combo', 'Large Combo', 'Combo for Two']],
            ['category' => 'Combos', 'name' => 'Student Combo', 'type' => 'combo', 'price' => 299, 'ingredients' => 'Mini chicken burger, French fries and soft drink.', 'description' => 'A mini chicken burger with fries and a refreshing drink.', 'minutes' => 15, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1692737348416-0a4fa4cf1523?auto=format&fit=crop&w=480&q=80', 'variants' => ['Classic Burger', 'Crispy Burger', 'Beef Burger', 'Extra Fries', 'Double Burger']],
            ['category' => 'Set Meals', 'name' => 'Chicken Set Meal', 'type' => 'set_meal', 'price' => 350, 'ingredients' => 'Fried rice, fried chicken, vegetables and salad.', 'description' => 'A complete meal with fried rice, chicken and fresh vegetables.', 'minutes' => 22, 'featured' => true, 'image' => 'https://prcdn.freetls.fastly.net/release_image/167099/7/167099-7-e0574fd57dd7af399c8fd6ae6e9f44d42-733x572.jpg?auto=webp&fit=bounds&w=480', 'variants' => ['Regular', 'Large', 'With Soft Drink', 'Extra Chicken', 'Meal for Two']],
            ['category' => 'Set Meals', 'name' => 'Beef Set Meal', 'type' => 'set_meal', 'price' => 420, 'ingredients' => 'Fried rice, beef curry, mixed vegetables and salad.', 'description' => 'A hearty beef meal served with rice, vegetables and salad.', 'minutes' => 25, 'featured' => true, 'image' => 'https://recipes.milon.im/assets/images/recipes/beef-tehari.jpg', 'variants' => ['Regular', 'Large', 'With Soft Drink', 'Extra Beef', 'Meal for Two']],
            ['category' => 'Beverages', 'name' => 'Soft Drink', 'type' => 'regular', 'price' => 60, 'ingredients' => 'Chilled carbonated soft drink.', 'description' => 'A chilled fizzy drink served cold.', 'minutes' => 2, 'featured' => false, 'image' => 'https://fluxmusic.cdn.radiosphere.io/images/7da5d489-aa2b-46d6-b91d-f1a60f4ad4e0_1732110432?type=large', 'variants' => ['250 ml', '500 ml', '1 Litre', 'With Ice', 'Party Bottle']],
            ['category' => 'Beverages', 'name' => 'Fresh Lemon Juice', 'type' => 'regular', 'price' => 120, 'ingredients' => 'Fresh lemon, sugar, water and ice.', 'description' => 'Freshly squeezed lemon juice served over ice.', 'minutes' => 5, 'featured' => true, 'image' => 'https://sprouttherapy.ca/cdn/shop/files/ChatGPT_Image_Mar_27_2026_11_42_45_AM_copy_1024x1024.png?v=1774622873', 'variants' => ['Regular', 'Large', 'Mint Lemonade', 'Sparkling Lemon', 'Less Sugar']],
            ['category' => 'Beverages', 'name' => 'Cold Coffee', 'type' => 'regular', 'price' => 180, 'ingredients' => 'Coffee, milk, sugar, ice and cream.', 'description' => 'Creamy chilled coffee blended with milk and ice.', 'minutes' => 8, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1581996323441-538096e854b9?auto=format&fit=crop&w=480&q=80', 'variants' => ['Regular', 'Large', 'Mocha', 'With Ice Cream', 'Extra Cream']],
            ['category' => 'Desserts', 'name' => 'Chocolate Brownie', 'type' => 'regular', 'price' => 180, 'ingredients' => 'Chocolate, flour, butter, sugar and egg.', 'description' => 'Rich, fudgy chocolate brownie served warm.', 'minutes' => 8, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=480&q=80', 'variants' => ['Regular', 'With Ice Cream', 'Double Chocolate', 'Walnut Brownie', 'Box of Four']],
            ['category' => 'Desserts', 'name' => 'Vanilla Ice Cream', 'type' => 'regular', 'price' => 130, 'ingredients' => 'Milk, cream, sugar and vanilla.', 'description' => 'Smooth vanilla ice cream made with fresh cream.', 'minutes' => 3, 'featured' => true, 'image' => 'https://www.isleofskyeseasalt.co.uk/cdn/shop/articles/20240520111433-ice-cream-sea-salt_93bc0572-ee9b-4fe3-be04-1ea1aaf28cba.jpg?v=1758612887&width=480', 'variants' => ['One Scoop', 'Two Scoops', 'Three Scoops', 'Brownie Sundae', 'Family Cup']],
        ];
    }

    private function requireTables($db, array $tables): void
    {
        foreach ($tables as $table) {
            if (!$db->getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException("Required tenant table [{$table}] is missing. Run tenant migrations first.");
            }
        }
    }
}
