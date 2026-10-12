<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->table(
                'purchase_order_receipt_items',
                function (Blueprint $table): void {

                    $table->string(
                        'supplier_batch_no',
                        120
                    )
                        ->nullable()
                        ->after(
                            'notes'
                        );

                    $table->date(
                        'manufactured_at'
                    )
                        ->nullable()
                        ->after(
                            'supplier_batch_no'
                        );

                    $table->date(
                        'expiry_date'
                    )
                        ->nullable()
                        ->after(
                            'manufactured_at'
                        );


                    $table->index(
                        [
                            'raw_material_id',
                            'expiry_date',
                        ],
                        'po_receipt_item_material_expiry_index'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'purchase_order_receipt_items',
                function (Blueprint $table): void {

                    $table->dropIndex(
                        'po_receipt_item_material_expiry_index'
                    );


                    $table->dropColumn([
                        'supplier_batch_no',
                        'manufactured_at',
                        'expiry_date',
                    ]);
                }
            );
    }
};
