<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('offers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('automatic');
            $table->string('discount_type', 20)->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->json('days_of_week')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->unsignedInteger('minimum_customer_orders')->default(0);
            $table->unsignedTinyInteger('birthday_window_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'type']);
            $table->index(['valid_from', 'valid_until']);
        });

        Schema::connection('tenant')->table('customers', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable()->after('email');
        });

        Schema::connection('tenant')->table('orders', function (Blueprint $table): void {
            $table->foreignId('offer_id')
                ->nullable()
                ->after('discount_amount')
                ->constrained('offers')
                ->nullOnDelete();
            $table->decimal('offer_discount_amount', 15, 2)
                ->default(0)
                ->after('offer_id');
            $table->index(['offer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('orders', function (Blueprint $table): void {
            $table->dropIndex(['offer_id', 'created_at']);
            $table->dropConstrainedForeignId('offer_id');
            $table->dropColumn('offer_discount_amount');
        });

        Schema::connection('tenant')->table('customers', function (Blueprint $table): void {
            $table->dropColumn('date_of_birth');
        });

        Schema::connection('tenant')->dropIfExists('offers');
    }
};
