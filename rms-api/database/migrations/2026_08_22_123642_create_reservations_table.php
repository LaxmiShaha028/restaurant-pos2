<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {

        Schema::create('reservations', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Reservation Number
            |--------------------------------------------------------------------------
            */

            $table->string(
                'reservation_no'
            )
            ->unique();



            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'customer_id'
            )
            ->nullable()
            ->constrained()
            ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Reservation Time
            |--------------------------------------------------------------------------
            */

            $table->date(
                'reservation_date'
            );


            $table->dateTime(
                'start_time'
            );


            $table->dateTime(
                'end_time'
            );



            /*
            |--------------------------------------------------------------------------
            | Guest
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'guest_count'
            );



            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'advance_amount',
                10,
                2
            )
            ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'reserved',
                    'confirmed',
                    'seated',
                    'completed',
                    'cancelled',
                ]
            )
            ->default('reserved');



            /*
            |--------------------------------------------------------------------------
            | Note
            |--------------------------------------------------------------------------
            */

            $table->text(
                'note'
            )
            ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Created By
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'created_by'
            )
            ->nullable();



            $table->timestamps();

            $table->softDeletes();


        });

    }



    public function down(): void
    {

        Schema::dropIfExists(
            'reservations'
        );

    }

};