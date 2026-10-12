<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends CentralModel
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    */

    protected $connection = 'central';


    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'payment_methods';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'code',
        'account_number',
        'instructions',
        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'status' =>
                'boolean',
        ];
    }
}