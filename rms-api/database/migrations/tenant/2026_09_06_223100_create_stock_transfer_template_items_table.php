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
                'stock_transfer_template_items',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'stock_transfer_template_id'
                    )
                        ->constrained(
                            'stock_transfer_templates',
                            indexName: 'stti_template_fk'
                        )
                        ->cascadeOnDelete();

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->constrained(
                            'raw_materials',
                            indexName: 'stti_material_fk'
                        )
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Display Snapshot
                    |--------------------------------------------------------------------------
                    |
                    | Template items are editable configuration. The snapshot keeps
                    | the template understandable if a raw material is later
                    | deactivated or soft-deleted.
                    |
                    */

                    $table->string(
                        'item_name',
                        255
                    );

                    $table->string(
                        'unit',
                        30
                    );

                    $table->decimal(
                        'quantity',
                        18,
                        4
                    )->unsigned();

                    $table->string(
                        'notes',
                        500
                    )->nullable();

                    $table->unsignedSmallInteger(
                        'sort_order'
                    )->default(0);

                    $table->timestamps();

                    $table->unique(
                        [
                            'stock_transfer_template_id',
                            'raw_material_id',
                        ],
                        'transfer_template_material_unique'
                    );

                    $table->index(
                        [
                            'stock_transfer_template_id',
                            'sort_order',
                        ],
                        'transfer_template_sort_index'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'stock_transfer_template_items'
            );
    }
};
