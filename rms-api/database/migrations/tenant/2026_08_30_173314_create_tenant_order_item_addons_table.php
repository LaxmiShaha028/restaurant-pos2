<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->create(
                'order_item_addons',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'order_item_id'
                    )
                        ->constrained(
                            'order_items'
                        )
                        ->cascadeOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Historical Add-on Reference
                    |--------------------------------------------------------------------------
                    |
                    | Existing schema uses menu_addon_id instead of add_on_id.
                    |
                    */

                    $table->unsignedBigInteger(
                        'menu_addon_id'
                    )->nullable();

                    $table->string(
                        'addon_name',
                        150
                    );

                    $table->decimal(
                        'unit_price',
                        15,
                        2
                    )->default(0);

                    $table->unsignedInteger(
                        'quantity'
                    )->default(1);

                    $table->decimal(
                        'total_price',
                        15,
                        2
                    )->default(0);

                    $table->timestamps();

                    $table->index(
                        'menu_addon_id'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'order_item_addons'
            );
    }
};