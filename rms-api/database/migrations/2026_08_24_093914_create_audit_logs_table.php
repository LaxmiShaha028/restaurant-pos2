<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {

        Schema::create(
            'audit_logs',
            function (Blueprint $table) {


                $table->id();



                /*
                |--------------------------------------------------------------------------
                | User who performed action
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'user_id'
                )
                ->nullable()
                ->constrained(
                    'users'
                )
                ->nullOnDelete();





                /*
                |--------------------------------------------------------------------------
                | Module Information
                |--------------------------------------------------------------------------
                */


                $table->string(
                    'module',
                    100
                );



                /*
                |--------------------------------------------------------------------------
                | Action
                |--------------------------------------------------------------------------
                */


                $table->string(
                    'action',
                    100
                );




                /*
                |--------------------------------------------------------------------------
                | Human readable message
                |--------------------------------------------------------------------------
                */


                $table->text(
                    'description'
                )
                ->nullable();






                /*
                |--------------------------------------------------------------------------
                | Model Information
                |--------------------------------------------------------------------------
                */


                $table->string(
                    'model_type'
                )
                ->nullable();



                $table->unsignedBigInteger(
                    'model_id'
                )
                ->nullable();






                /*
                |--------------------------------------------------------------------------
                | Old and New Data
                |--------------------------------------------------------------------------
                */


                $table->json(
                    'old_values'
                )
                ->nullable();



                $table->json(
                    'new_values'
                )
                ->nullable();







                /*
                |--------------------------------------------------------------------------
                | Security Information
                |--------------------------------------------------------------------------
                */


                $table->ipAddress(
                    'ip_address'
                )
                ->nullable();



                $table->text(
                    'user_agent'
                )
                ->nullable();




                $table->timestamps();



                /*
                |--------------------------------------------------------------------------
                | Index
                |--------------------------------------------------------------------------
                */


                $table->index([
                    'module',
                    'action'
                ]);


                $table->index(
                    'created_at'
                );


            }
        );

    }




    public function down(): void
    {

        Schema::dropIfExists(
            'audit_logs'
        );

    }


};