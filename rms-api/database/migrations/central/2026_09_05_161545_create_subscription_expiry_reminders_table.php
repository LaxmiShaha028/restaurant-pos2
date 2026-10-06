<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create(
            'subscription_expiry_reminders',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->unsignedBigInteger('restaurant_id');
                $table->string('reminder_type', 20);
                $table->dateTime('subscription_end_at');
                $table->string('recipient_email');
                $table->dateTime('sent_at');
                $table->timestamps();

                $table->unique(
                    [
                        'subscription_id',
                        'reminder_type',
                        'subscription_end_at',
                    ],
                    'subscription_expiry_reminder_unique'
                );

                $table->index(
                    [
                        'restaurant_id',
                        'subscription_end_at',
                    ],
                    'subscription_expiry_reminder_restaurant_end_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists('subscription_expiry_reminders');
    }
};
