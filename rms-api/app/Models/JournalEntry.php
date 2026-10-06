<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JournalEntry extends Model
{
    use HasFactory;
    protected $connection = 'tenant';


    public const TYPE_MANUAL =
        'manual';

    public const TYPE_SOURCE =
        'source';

    public const TYPE_REVERSAL =
        'reversal';


    public const STATUS_POSTED =
        'posted';

    public const STATUS_REVERSED =
        'reversed';


    protected $fillable = [

        'journal_number',

        'journal_date',

        'entry_type',

        'status',

        'description',

        'source_type',

        'source_id',

        'source_revision',

        'source_hash',

        'reversal_of_id',

        'total_debit',

        'total_credit',

        'posted_at',

        'posted_by',

        'reversed_at',

        'reversed_by',

        'reversal_reason',

    ];


    protected $casts = [

        'journal_date' =>
            'date',

        'source_id' =>
            'integer',

        'source_revision' =>
            'integer',

        'reversal_of_id' =>
            'integer',

        'total_debit' =>
            'decimal:2',

        'total_credit' =>
            'decimal:2',

        'posted_at' =>
            'datetime',

        'posted_by' =>
            'integer',

        'reversed_at' =>
            'datetime',

        'reversed_by' =>
            'integer',

    ];


    public function lines(): HasMany
    {
        return $this->hasMany(
            JournalLine::class,
            'journal_entry_id'
        )
            ->orderBy(
                'id'
            );
    }


    public function poster(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }


    public function reverser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reversed_by'
        );
    }


    public function originalEntry(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reversal_of_id'
        );
    }


    public function reversalEntry(): HasOne
    {
        return $this->hasOne(
            self::class,
            'reversal_of_id'
        );
    }


    public function isPosted(): bool
    {
        return (
            $this->status ===
            self::STATUS_POSTED
        );
    }


    public function isReversed(): bool
    {
        return (
            $this->status ===
            self::STATUS_REVERSED
        );
    }


    public function isReversal(): bool
    {
        return (
            $this->entry_type ===
            self::TYPE_REVERSAL
        );
    }


    public function canReverse(): bool
    {
        return (
            $this->isPosted()
            &&
            ! $this->isReversal()
            &&
            $this->reversal_of_id ===
            null
            &&
            ! $this->reversalEntry()
                ->exists()
        );
    }
}
