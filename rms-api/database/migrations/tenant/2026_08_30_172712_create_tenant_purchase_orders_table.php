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
                'purchase_orders',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'supplier_id'
                    )
                        ->constrained(
                            'suppliers'
                        )
                        ->restrictOnDelete();

                    $table->dateTime(
                        'order_date'
                    );

                    $table->date(
                        'delivery_date'
                    )->nullable();

                    $table->string(
                        'status',
                        50
                    )->default('ordered');

                    $table->decimal(
                        'subtotal',
                        14,
                        2
                    )->default(0);

                    $table->decimal(
                        'tax',
                        14,
                        2
                    )->default(0);

                    $table->decimal(
                        'service_charge',
                        14,
                        2
                    )->default(0);

                    $table->decimal(
                        'total_amount',
                        14,
                        2
                    )->default(0);

                    $table->decimal(
                        'paid_amount',
                        14,
                        2
                    )->default(0);

                    $table->decimal(
                        'due_amount',
                        14,
                        2
                    )->default(0);

                    $table->string(
                        'payment_method',
                        50
                    )->nullable();

                    $table->foreignId(
                        'ordered_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->text(
                        'notes'
                    )->nullable();

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

                    $table->softDeletes();

                    $table->index([
                        'supplier_id',
                        'order_date',
                    ]);

                    $table->index(
                        'status'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('purchase_orders');
    }
};