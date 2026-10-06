<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StockTransferTemplateItem extends Model
{
    use HasFactory;


    protected $fillable = [

        'stock_transfer_template_id',

        'raw_material_id',

        'item_name',

        'unit',

        'quantity',

        'notes',

        'sort_order',

    ];


    protected $casts = [

        'stock_transfer_template_id' =>
            'integer',

        'raw_material_id' =>
            'integer',

        'quantity' =>
            'decimal:4',

        'sort_order' =>
            'integer',

    ];


    public function template(): BelongsTo
    {
        return $this->belongsTo(
            StockTransferTemplate::class,
            'stock_transfer_template_id'
        );
    }


    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(
            RawMaterial::class,
            'raw_material_id'
        )->withTrashed();
    }
}
