<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')
            ->create(
                'payment_methods',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'name',
                        100
                    );

                    $table->string(
                        'code',
                        50
                    )->unique();

                    $table->string(
                        'account_number',
                        100
                    )->nullable();

                    $table->text(
                        'instructions'
                    )->nullable();

                    $table->boolean(
                        'status'
                    )->default(true);

                    $table->timestamps();
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'payment_methods'
            );
    }
};