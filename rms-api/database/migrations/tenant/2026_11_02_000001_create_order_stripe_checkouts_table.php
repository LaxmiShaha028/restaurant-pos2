<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_stripe_checkouts', function (Blueprint $table): void {
            $table->id();
            $table->string('checkout_session_id')->unique();
            $table->string('payment_intent_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('bdt');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->json('order_payload');
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_stripe_checkouts');
    }
};
