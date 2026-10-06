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
                'purchase_orders',
                function (Blueprint $table): void {

                    $table->decimal(
                        'return_credit_applied',
                        18,
                        2
                    )
                        ->default(0)
                        ->after('paid_amount');
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'purchase_orders',
                function (Blueprint $table): void {

                    $table->dropColumn(
                        'return_credit_applied'
                    );
                }
            );
    }
};
