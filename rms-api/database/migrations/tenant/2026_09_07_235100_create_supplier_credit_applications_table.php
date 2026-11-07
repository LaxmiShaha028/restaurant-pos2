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
        if (
            Schema::connection('tenant')
                ->hasTable(
                    'supplier_credit_applications'
                )
        ) {
            return;
        }


        Schema::connection('tenant')
            ->create(
                'supplier_credit_applications',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'application_no',
                        80
                    )->unique();

                    /*
                    |--------------------------------------------------------------------------
                    | Credit Source
                    |--------------------------------------------------------------------------
                    |
                    | Must point to a PurchaseReturnSettlement row whose type is:
                    | supplier_credit
                    |
                    */

                    $table->unsignedBigInteger(
                        'source_settlement_id'
                    );

                    $table->unsignedBigInteger(
                        'supplier_id'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Target Purchase Order
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedBigInteger(
                        'purchase_order_id'
                    );

                    $table->decimal(
                        'amount',
                        18,
                        2
                    )->unsigned();

                    $table->timestamp(
                        'applied_at'
                    );

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'created_by'
                    )->nullable();

                    $table->timestamps();


                    /*
                    |--------------------------------------------------------------------------
                    | Explicit SHORT Keys
                    |--------------------------------------------------------------------------
                    */

                    $table->foreign(
                        'source_settlement_id',
                        $this->tenantConstraintName('sca_source_fk')
                    )
                        ->references('id')
                        ->on('purchase_return_settlements')
                        ->restrictOnDelete();

                    $table->foreign(
                        'supplier_id',
                        $this->tenantConstraintName('sca_supplier_fk')
                    )
                        ->references('id')
                        ->on('suppliers')
                        ->restrictOnDelete();

                    $table->foreign(
                        'purchase_order_id',
                        $this->tenantConstraintName('sca_po_fk')
                    )
                        ->references('id')
                        ->on('purchase_orders')
                        ->restrictOnDelete();

                    $table->foreign(
                        'created_by',
                        $this->tenantConstraintName('sca_user_fk')
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->index(
                        [
                            'source_settlement_id',
                            'applied_at',
                        ],
                        'sca_source_date_idx'
                    );

                    $table->index(
                        [
                            'supplier_id',
                            'applied_at',
                        ],
                        'sca_supplier_date_idx'
                    );

                    $table->index(
                        [
                            'purchase_order_id',
                            'applied_at',
                        ],
                        'sca_po_date_idx'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'supplier_credit_applications'
            );
    }
};
