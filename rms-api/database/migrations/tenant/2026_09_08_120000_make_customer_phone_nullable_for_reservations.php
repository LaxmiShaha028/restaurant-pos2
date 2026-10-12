<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Name-only Reservation Customers
    |--------------------------------------------------------------------------
    |
    | Reservation booking may create a lightweight customer with only a name.
    | The existing UNIQUE phone index is preserved. MySQL allows multiple NULL
    | values in a UNIQUE index.
    |
    */

    public function up(): void
    {
        Schema::connection(
            'tenant'
        )
            ->table(
                'customers',
                function (
                    Blueprint $table
                ): void {

                    $table->string(
                        'phone',
                        30
                    )
                        ->nullable()
                        ->change();
                }
            );
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Safe Down Migration
        |--------------------------------------------------------------------------
        |
        | Do not make phone NOT NULL while name-only reservation customers still
        | exist because that would fail and/or destroy valid data.
        |
        | If you ever need to reverse this migration, first backfill phone values
        | and then create a separate explicit migration.
        |
        */
    }
};
