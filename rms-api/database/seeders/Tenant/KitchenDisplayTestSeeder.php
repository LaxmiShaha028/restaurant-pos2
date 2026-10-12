<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KitchenDisplayTestSeeder extends Seeder
{
    private const ORDER_PREFIX = 'TEST-KITCHEN-';

    public function run(): int
    {
        $connection = DB::connection('tenant');
        $timestamp = now();

        return $connection->transaction(function () use ($connection, $timestamp): int {
            $existingOrderIds = $connection->table('orders')
                ->where('order_number', 'like', self::ORDER_PREFIX . '%')
                ->pluck('id');

            if ($existingOrderIds->isNotEmpty()) {
                $connection->table('order_items')
                    ->whereIn('order_id', $existingOrderIds)
                    ->delete();

                $connection->table('order_kitchen_batches')
                    ->whereIn('order_id', $existingOrderIds)
                    ->delete();

                $connection->table('orders')
                    ->whereIn('id', $existingOrderIds)
                    ->delete();
            }

            for ($number = 1; $number <= 12; $number++) {
                $createdAt = $timestamp->copy()->subMinutes(12 - $number);
                $orderNumber = sprintf(
                    '%s%s-%02d',
                    self::ORDER_PREFIX,
                    $timestamp->format('YmdHis'),
                    $number,
                );

                $orderId = $connection->table('orders')->insertGetId([
                    'order_number' => $orderNumber,
                    'customer_name' => 'Kitchen Pagination Test',
                    'status' => 'pending',
                    'subtotal' => 120,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'service_charge' => 0,
                    'total_amount' => 120,
                    'paid_amount' => 0,
                    'due_amount' => 120,
                    'payment_status' => 'due',
                    'order_note' => 'Test order for Kitchen Display pagination.',
                    'kitchen_note' => 'Pagination test order.',
                    'sent_to_kitchen_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $batchId = $connection->table('order_kitchen_batches')->insertGetId([
                    'order_id' => $orderId,
                    'batch_no' => 1,
                    'status' => 'pending',
                    'sent_to_kitchen_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $connection->table('order_items')->insert([
                    'order_id' => $orderId,
                    'order_kitchen_batch_id' => $batchId,
                    'item_name' => sprintf('Pagination Test Meal %02d', $number),
                    'unit_price' => 120,
                    'quantity' => 1,
                    'addon_total' => 0,
                    'line_total' => 120,
                    'status' => 'pending',
                    'kitchen_note' => 'Test item for pagination.',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            return 12;
        });
    }
}
