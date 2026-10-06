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
                'journal_entries',
                function (Blueprint $table): void {

                    $table->string(
                        'source_hash',
                        64
                    )
                        ->nullable()
                        ->after(
                            'source_revision'
                        );

                    $table->index(
                        'source_hash',
                        'journal_entries_source_hash_idx'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'journal_entries',
                function (Blueprint $table): void {

                    $table->dropIndex(
                        'journal_entries_source_hash_idx'
                    );

                    $table->dropColumn(
                        'source_hash'
                    );
                }
            );
    }
};
