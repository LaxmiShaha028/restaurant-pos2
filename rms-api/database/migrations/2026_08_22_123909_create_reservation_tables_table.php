<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {

        Schema::create(
            'reservation_tables',
            function(Blueprint $table){


                $table->id();



                $table->foreignId(
                    'reservation_id'
                )
                ->constrained()
                ->cascadeOnDelete();



                $table->foreignId(
                    'restaurant_table_id'
                )
                ->constrained()
                ->cascadeOnDelete();



                $table->timestamps();


            }
        );

    }



    public function down(): void
    {

        Schema::dropIfExists(
            'reservation_tables'
        );

    }

};