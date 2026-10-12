<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->create(
                'journal_entries',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'journal_number',
                        40
                    )
                        ->nullable()
                        ->unique();

                    $table->date(
                        'journal_date'
                    );

                    $table->string(
                        'entry_type',
                        20
                    )
                        ->default(
                            'manual'
                        );

                    $table->string(
                        'status',
                        20
                    )
                        ->default(
                            'posted'
                        );

                    $table->text(
                        'description'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Source Versioning
                    |--------------------------------------------------------------------------
                    |
                    | Manual journals keep these null.
                    |
                    | Future examples:
                    | source_type     = income
                    | source_id       = 15
                    | source_revision = 1
                    |
                    | If source #15 changes later:
                    | old journal is reversed and revision 2 is posted.
                    |
                    */

                    $table->string(
                        'source_type',
                        50
                    )->nullable();

                    $table->unsignedBigInteger(
                        'source_id'
                    )->nullable();

                    $table->unsignedInteger(
                        'source_revision'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'reversal_of_id'
                    )
                        ->nullable()
                        ->unique();

                    $table->decimal(
                        'total_debit',
                        16,
                        2
                    );

                    $table->decimal(
                        'total_credit',
                        16,
                        2
                    );

                    $table->timestamp(
                        'posted_at'
                    );

                    $table->unsignedBigInteger(
                        'posted_by'
                    );

                    $table->timestamp(
                        'reversed_at'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'reversed_by'
                    )->nullable();

                    $table->string(
                        'reversal_reason',
                        500
                    )->nullable();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Intentionally NO SoftDeletes
                    |--------------------------------------------------------------------------
                    |
                    | Posted journals are permanent accounting history.
                    |
                    */

                    $table->unique(
                        [
                            'source_type',
                            'source_id',
                            'source_revision',
                        ],
                        'journal_source_revision_unique'
                    );

                    $table->index(
                        'journal_date',
                        'journal_entries_date_idx'
                    );

                    $table->index(
                        'status',
                        'journal_entries_status_idx'
                    );

                    $table->index(
                        'entry_type',
                        'journal_entries_type_idx'
                    );

                    $table->index(
                        [
                            'source_type',
                            'source_id',
                        ],
                        'journal_entries_source_idx'
                    );
                }
            );


        Schema::connection('tenant')
            ->create(
                'journal_lines',
                function (Blueprint $table): void {

                    $table->id();

                    $table->unsignedBigInteger(
                        'journal_entry_id'
                    );

                    $table->unsignedBigInteger(
                        'ledger_account_id'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Account Snapshot
                    |--------------------------------------------------------------------------
                    |
                    | Historical journal display does not change if account master
                    | data is renamed later.
                    |
                    */

                    $table->string(
                        'account_code_snapshot',
                        20
                    );

                    $table->string(
                        'account_name_snapshot',
                        150
                    );

                    $table->string(
                        'account_type_snapshot',
                        20
                    );

                    $table->string(
                        'normal_balance_snapshot',
                        10
                    );

                    $table->decimal(
                        'debit',
                        16,
                        2
                    )->default(0);

                    $table->decimal(
                        'credit',
                        16,
                        2
                    )->default(0);

                    $table->string(
                        'memo',
                        500
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        'journal_entry_id',
                        'journal_lines_entry_idx'
                    );

                    $table->index(
                        'ledger_account_id',
                        'journal_lines_account_idx'
                    );

                    $table->index(
                        [
                            'ledger_account_id',
                            'journal_entry_id',
                        ],
                        'journal_lines_account_entry_idx'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'journal_lines'
            );

        Schema::connection('tenant')
            ->dropIfExists(
                'journal_entries'
            );
    }
};
