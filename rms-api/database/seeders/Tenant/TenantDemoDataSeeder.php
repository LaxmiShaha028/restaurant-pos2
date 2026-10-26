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
                    'username' => "demo-staff-{$n}",
                    'email' => "demo-staff-{$n}@local.test",
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

            $categories = $db->table('menu_categories')->whereNull('deleted_at')->orderBy('id')->pluck('id')->all();
            if (!$categories) {
                for ($i = 1; $i <= 10; $i++) {
                    $categories[] = $db->table('menu_categories')->insertGetId([
                        'category_name' => self::PREFIX.'Category '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'description' => 'Local demo menu category.',
                        'is_available' => true,
                        'display_order' => $i,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $menuItems = [];
            for ($i = 1; $i <= $count; $i++) {
                $n = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $price = 180 + ($i % 15) * 25;
                $menuItems[] = [
                    'id' => $db->table('menu_items')->insertGetId([
                        'menu_category_id' => $categories[($i - 1) % count($categories)],
                        'menu_name' => self::PREFIX."Menu Item {$n}",
                        'item_type' => 'regular',
                        'price' => $price,
                        'ingredients' => 'Demo ingredients',
                        'description' => 'DEMO DATA: menu item for local module preview.',
                        'preparation_time' => 8 + ($i % 20),
                        'is_available' => true,
                        'is_featured' => $i % 10 === 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]),
                    'name' => self::PREFIX."Menu Item {$n}",
                    'price' => $price,
                ];
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

    private function requireTables($db, array $tables): void
    {
        foreach ($tables as $table) {
            if (!$db->getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException("Required tenant table [{$table}] is missing. Run tenant migrations first.");
            }
        }
    }
}
