<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    private function tenantConstraintName(string $name): string
    {
        return substr((string) config('database.connections.tenant.prefix', '') . $name, 0, 64);
    }

    public function up(): void
    {
        Schema::connection('tenant')->create(
            'purchase_return_items',
            function (Blueprint $table): void {
                $table->id();

                $table->unsignedBigInteger('purchase_return_id');
                $table->unsignedBigInteger('warehouse_stock_lot_id');
                $table->unsignedBigInteger('raw_material_id');

                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->unsignedBigInteger('purchase_order_receipt_id')->nullable();

                // Historical snapshots for audit/PDF.
                $table->string('item_name', 180);
                $table->string('unit', 30);
                $table->string('lot_no', 80);
                $table->string('supplier_batch_no', 120)->nullable();
                $table->date('manufactured_at')->nullable();
                $table->date('expiry_date')->nullable();

                $table->decimal('quantity', 18, 4)->unsigned();

                $table->decimal('lot_quantity_before', 18, 4)->unsigned();
                $table->decimal('lot_quantity_after', 18, 4)->unsigned();
                $table->decimal('warehouse_quantity_before', 18, 4)->unsigned();
                $table->decimal('warehouse_quantity_after', 18, 4)->unsigned();

                // Exact supplier/source cost vs RMS moving-average inventory cost.
                $table->decimal('return_unit_cost', 18, 4)->unsigned();
                $table->decimal('expected_supplier_credit', 18, 4)->unsigned();

                $table->decimal('inventory_average_unit_cost', 18, 4)->unsigned();
                $table->decimal('inventory_value_reduction', 18, 4)->unsigned();

                $table->decimal('valuation_variance', 18, 4);

                $table->string('reason', 60);
                $table->string('reason_notes', 500)->nullable();

                $table->timestamps();

                $table->foreign('purchase_return_id', $this->tenantConstraintName('pri_return_fk'))
                    ->references('id')->on('purchase_returns')->cascadeOnDelete();

                $table->foreign('warehouse_stock_lot_id', $this->tenantConstraintName('pri_lot_fk'))
                    ->references('id')->on('warehouse_stock_lots')->restrictOnDelete();

                $table->foreign('raw_material_id', $this->tenantConstraintName('pri_material_fk'))
                    ->references('id')->on('raw_materials')->restrictOnDelete();

                $table->foreign('purchase_order_id', $this->tenantConstraintName('pri_po_fk'))
                    ->references('id')->on('purchase_orders')->nullOnDelete();

                $table->foreign('purchase_order_receipt_id', $this->tenantConstraintName('pri_grn_fk'))
                    ->references('id')->on('purchase_order_receipts')->nullOnDelete();

                $table->index(
                    ['warehouse_stock_lot_id', 'created_at'],
                    'pri_lot_date_idx'
                );

                $table->index(
                    ['raw_material_id', 'created_at'],
                    'pri_material_date_idx'
                );

                $table->index('reason', 'pri_reason_idx');
            }
        );
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('purchase_return_items');
    }
};
