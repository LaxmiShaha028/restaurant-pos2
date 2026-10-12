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
                'inventory_wastages',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Human Readable Reference
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'wastage_no',
                        80
                    )->unique();

                    /*
                    |--------------------------------------------------------------------------
                    | Business Date
                    |--------------------------------------------------------------------------
                    */

                    $table->timestamp(
                        'wasted_at'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Accounting Snapshot
                    |--------------------------------------------------------------------------
                    |
                    | This is NOT a cash movement.
                    |
                    | Later accounting can post:
                    |
                    | Dr. Wastage / Inventory Loss
                    | Cr. Restaurant Inventory
                    |
                    */

                    $table->decimal(
                        'total_loss',
                        18,
                        4
                    )
                        ->unsigned()
                        ->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Notes / Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->foreignId(
                        'recorded_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        'wasted_at'
                    );

                    $table->index(
                        'recorded_by'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'inventory_wastages'
            );
    }
};
