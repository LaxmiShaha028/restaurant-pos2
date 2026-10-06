<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPaymentMapping extends Model
{
    use HasFactory;


    protected $fillable = [

        'payment_method',

        'ledger_account_id',

        'updated_by',

    ];


    protected $casts = [

        'ledger_account_id' =>
            'integer',

        'updated_by' =>
            'integer',

    ];


    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'ledger_account_id'
        );
    }


    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
