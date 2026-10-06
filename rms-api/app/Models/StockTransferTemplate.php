<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class StockTransferTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [

        'name',

        'description',

        'is_active',

        'created_by',

        'updated_by',

    ];


    protected $casts = [

        'is_active' =>
            'boolean',

        'created_by' =>
            'integer',

        'updated_by' =>
            'integer',

    ];


    public function items(): HasMany
    {
        return $this->hasMany(
            StockTransferTemplateItem::class,
            'stock_transfer_template_id'
        )
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'id'
            );
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
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
