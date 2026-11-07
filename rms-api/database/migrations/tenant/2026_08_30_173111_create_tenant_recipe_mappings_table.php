<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                'recipe_mappings',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Target
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'menu_item_id'
                    )
                        ->nullable()
                        ->constrained(
                            'menu_items'
                        )
                        ->cascadeOnDelete();

                    $table->unsignedBigInteger(
                        'variant_id'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'add_on_id'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Raw Material
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->constrained(
                            'raw_materials'
                        )
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'quantity',
                        14,
                        4
                    );

                    $table->string(
                        'unit',
                        30
                    );

                    $table->text(
                        'notes'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'updated_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Foreign Keys
                    |--------------------------------------------------------------------------
                    */

                    $table->foreign(
                        'variant_id',
                        $this->tenantConstraintName('recipe_mapping_variant_fk')
                    )
                        ->references('id')
                        ->on('menu_item_variants')
                        ->restrictOnDelete()
                        ->cascadeOnUpdate();

                    $table->foreign(
                        'add_on_id',
                        $this->tenantConstraintName('recipe_mapping_addon_fk')
                    )
                        ->references('id')
                        ->on('add_ons')
                        ->cascadeOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Supporting Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        'menu_item_id',
                        'recipe_mapping_menu_item_index'
                    );

                    $table->index(
                        'variant_id',
                        'recipe_mapping_variant_index'
                    );

                    $table->index(
                        'add_on_id',
                        'recipe_mapping_addon_index'
                    );

                    $table->index(
                        'raw_material_id',
                        'recipe_mapping_raw_material_index'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Menu Item Recipe Identity
                    |--------------------------------------------------------------------------
                    |
                    | Note:
                    | MySQL UNIQUE + nullable variant_id does not fully guarantee
                    | uniqueness for NULL values.
                    |
                    | The application model already performs duplicate protection.
                    |
                    */

                    $table->unique(
                        [
                            'menu_item_id',
                            'variant_id',
                            'raw_material_id',
                        ],
                        'recipe_mapping_menu_variant_material_unique'
                    );

                    $table->unique(
                        [
                            'add_on_id',
                            'raw_material_id',
                        ],
                        'recipe_mapping_addon_material_unique'
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Enforce Exactly One Target
        |--------------------------------------------------------------------------
        */

        $tenantPrefix = DB::connection('tenant')->getTablePrefix();

        DB::connection('tenant')->statement(
            'ALTER TABLE `' . $tenantPrefix . 'recipe_mappings` ' .
            'ADD CONSTRAINT `' . $tenantPrefix . 'recipe_mapping_target_check` ' .
            'CHECK (' .
            '(`menu_item_id` IS NOT NULL AND `add_on_id` IS NULL)' .
            ' OR ' .
            '(`menu_item_id` IS NULL AND `add_on_id` IS NOT NULL)' .
            ')'
        );
    }

    public function down(): void
    {
        $tenantPrefix = DB::connection('tenant')->getTablePrefix();

        Schema::connection('tenant')
            ->statement(
                'ALTER TABLE `' . $tenantPrefix . 'recipe_mappings` ' .
                'DROP CHECK `' . $tenantPrefix . 'recipe_mapping_target_check`'
            );

        Schema::connection('tenant')
            ->dropIfExists(
                'recipe_mappings'
            );
    }
};
