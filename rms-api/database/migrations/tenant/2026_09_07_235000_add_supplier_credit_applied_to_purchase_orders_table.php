<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::connection('tenant')
                ->hasColumn(
                    'purchase_orders',
                    'supplier_credit_applied'
                )
        ) {
            return;
        }


        Schema::connection('tenant')
            ->table(
                'purchase_orders',
                function (Blueprint $table): void {

                    $table->decimal(
                        'supplier_credit_applied',
                        18,
                        2
                    )
                        ->default(0)
                        ->after('return_credit_applied');
                }
            );
    }


    public function down(): void
    {
        if (
            !Schema::connection('tenant')
                ->hasColumn(
                    'purchase_orders',
                    'supplier_credit_applied'
                )
        ) {
            return;
        }


        Schema::connection('tenant')
            ->table(
                'purchase_orders',
                function (Blueprint $table): void {

                    $table->dropColumn(
                        'supplier_credit_applied'
                    );
                }
            );
    }
};
