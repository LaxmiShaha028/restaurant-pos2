<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->table(
                'warehouse_stock_lots',
                function (Blueprint $table): void {

                    /*
                    |--------------------------------------------------------------------------
                    | User-editable notes
                    |--------------------------------------------------------------------------
                    |
                    | The existing `notes` column contains system/source audit text.
                    | Never overwrite it from the UI.
                    |
                    */

                    $table->text(
                        'manual_notes'
                    )
                        ->nullable()
                        ->after(
                            'notes'
                        );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'warehouse_stock_lots',
                function (Blueprint $table): void {

                    $table->dropColumn(
                        'manual_notes'
                    );
                }
            );
    }
};
