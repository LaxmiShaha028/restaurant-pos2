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
            'purchase_returns',
            function (Blueprint $table): void {
                $table->id();

                $table->string('return_no', 80)->unique();

                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('supplier_name', 180);
                $table->string('supplier_phone', 80)->nullable();
                $table->string('supplier_email', 180)->nullable();
                $table->text('supplier_address')->nullable();

                $table->timestamp('returned_at');
                $table->unsignedInteger('total_items')->default(0);

                // Physical return/accounting preparation snapshots.
                $table->decimal('expected_supplier_credit', 18, 4)->default(0);
                $table->decimal('inventory_value_reduction', 18, 4)->default(0);
                $table->decimal('valuation_variance', 18, 4)->default(0);

                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                // Explicit short names avoid MySQL's 64-character identifier limit.
                $table->foreign('supplier_id', $this->tenantConstraintName('pr_supplier_fk'))
                    ->references('id')->on('suppliers')->nullOnDelete();

                $table->foreign('created_by', $this->tenantConstraintName('pr_creator_fk'))
                    ->references('id')->on('users')->nullOnDelete();

                $table->index('returned_at', 'pr_returned_at_idx');
                $table->index(
                    ['supplier_id', 'returned_at'],
                    'pr_supplier_date_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('purchase_returns');
    }
};
