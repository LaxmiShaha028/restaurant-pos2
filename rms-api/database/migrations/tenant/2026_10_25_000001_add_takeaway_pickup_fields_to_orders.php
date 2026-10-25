<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('orders', function (Blueprint $table): void {
            $table->string('fulfillment_type', 20)->default('dine_in')->after('reservation_id');
            $table->string('pickup_token', 20)->nullable()->unique()->after('fulfillment_type');
            $table->string('pickup_status', 20)->nullable()->after('pickup_token');
            $table->dateTime('picked_up_at')->nullable()->after('pickup_status');
            $table->index(['fulfillment_type', 'status', 'pickup_status'], 'orders_pickup_board_index');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_pickup_board_index');
            $table->dropUnique(['pickup_token']);
            $table->dropColumn([
                'fulfillment_type',
                'pickup_token',
                'pickup_status',
                'picked_up_at',
            ]);
        });
    }
};
