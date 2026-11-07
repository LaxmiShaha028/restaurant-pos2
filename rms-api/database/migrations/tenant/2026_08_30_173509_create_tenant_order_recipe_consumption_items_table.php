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
                'order_recipe_consumption_items',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Consumption
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'order_recipe_consumption_id'
                    );

                    $table->foreign(
                        'order_recipe_consumption_id',
                        $this->tenantConstraintName('orci_consumption_fk')
                    )
                        ->references('id')
                        ->on('order_recipe_consumptions')
                        ->cascadeOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Raw Material
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'raw_material_id'
                    );

                    $table->foreign(
                        'raw_material_id',
                        $this->tenantConstraintName('orci_material_fk')
                    )
                        ->references('id')
                        ->on('raw_materials')
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Material Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'material_name',
                        255
                    );

                    $table->string(
                        'unit',
                        30
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Consumption
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'quantity',
                        14,
                        4
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Cost Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'unit_cost',
                        14,
                        4
                    )->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Restaurant Stock Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'restaurant_quantity_before',
                        14,
                        4
                    );

                    $table->decimal(
                        'restaurant_quantity_after',
                        14,
                        4
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Recipe Source Breakdown
                    |--------------------------------------------------------------------------
                    */

                    $table->json(
                        'source_breakdown'
                    )->nullable();

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Unique Ingredient Per Consumption
                    |--------------------------------------------------------------------------
                    */

                    $table->unique(
                        [
                            'order_recipe_consumption_id',
                            'raw_material_id',
                        ],
                        'orci_consumption_material_unique'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Lookup Index
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        'raw_material_id',
                        'orci_material_idx'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'order_recipe_consumption_items'
            );
    }
};
