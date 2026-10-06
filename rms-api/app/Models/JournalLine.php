<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $fillable = [

        'journal_entry_id',

        'ledger_account_id',

        'account_code_snapshot',

        'account_name_snapshot',

        'account_type_snapshot',

        'normal_balance_snapshot',

        'debit',

        'credit',

        'memo',

    ];


    protected $casts = [

        'journal_entry_id' =>
            'integer',

        'ledger_account_id' =>
            'integer',

        'debit' =>
            'decimal:2',

        'credit' =>
            'decimal:2',

    ];


    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntry::class,
            'journal_entry_id'
        );
    }


    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'ledger_account_id'
        );
    }
}
