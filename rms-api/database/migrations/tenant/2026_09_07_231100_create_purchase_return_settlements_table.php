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
        Schema::connection('tenant')
            ->create(
                'purchase_return_settlements',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'settlement_no',
                        80
                    )->unique();

                    $table->unsignedBigInteger(
                        'purchase_return_id'
                    );

                    $table->unsignedBigInteger(
                        'supplier_id'
                    );

                    $table->string(
                        'settlement_type',
                        60
                    );

                    $table->decimal(
                        'amount',
                        18,
                        2
                    )->unsigned();

                    $table->unsignedBigInteger(
                        'purchase_order_id'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'parent_settlement_id'
                    )->nullable();

                    $table->string(
                        'payment_method',
                        50
                    )->nullable();

                    $table->string(
                        'transaction_reference',
                        255
                    )->nullable();

                    $table->timestamp(
                        'settled_at'
                    );

                    $table->boolean(
                        'allocation_effect'
                    )->default(true);

                    $table->boolean(
                        'cash_effect'
                    )->default(false);

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'created_by'
                    )->nullable();

                    $table->timestamps();


                    /*
                    |--------------------------------------------------------------------------
                    | Explicit SHORT keys
                    |--------------------------------------------------------------------------
                    */

                    $table->foreign(
                        'purchase_return_id',
                        $this->tenantConstraintName('prs_return_fk')
                    )
                        ->references('id')
                        ->on('purchase_returns')
                        ->restrictOnDelete();

                    $table->foreign(
                        'supplier_id',
                        $this->tenantConstraintName('prs_supplier_fk')
                    )
                        ->references('id')
                        ->on('suppliers')
                        ->restrictOnDelete();

                    $table->foreign(
                        'purchase_order_id',
                        $this->tenantConstraintName('prs_po_fk')
                    )
                        ->references('id')
                        ->on('purchase_orders')
                        ->nullOnDelete();

                    $table->foreign(
                        'parent_settlement_id',
                        $this->tenantConstraintName('prs_parent_fk')
                    )
                        ->references('id')
                        ->on('purchase_return_settlements')
                        ->restrictOnDelete();

                    $table->foreign(
                        'created_by',
                        $this->tenantConstraintName('prs_user_fk')
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->index(
                        [
                            'purchase_return_id',
                            'settled_at',
                        ],
                        'prs_return_date_idx'
                    );

                    $table->index(
                        [
                            'supplier_id',
                            'settled_at',
                        ],
                        'prs_supplier_date_idx'
                    );

                    $table->index(
                        'settlement_type',
                        'prs_type_idx'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'purchase_return_settlements'
            );
    }
};
