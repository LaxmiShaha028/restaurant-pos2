<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        /*
        |----------------------------------------------------------------------
        | Restaurant Identity
        |----------------------------------------------------------------------
        */

        'restaurant_name',
        'restaurant_logo',
        'landing_page_settings',

        /*
        |----------------------------------------------------------------------
        | Business / Location
        |----------------------------------------------------------------------
        */

        'country',
        'branch_address',

        /*
        |----------------------------------------------------------------------
        | Tax & Service Charge
        |----------------------------------------------------------------------
        */

        'vat_rate',
        'service_charge_rate',

        'vat_type',
'service_charge_type',

        /*
        |----------------------------------------------------------------------
        | Business Verification
        |----------------------------------------------------------------------
        */

        'owner_nid',
        'owner_trade_license',

        /*
        |----------------------------------------------------------------------
        | Invoice General
        |----------------------------------------------------------------------
        */

        'invoice_subtitle',
        'bin_number',

        /*
        |----------------------------------------------------------------------
        | Invoice Print Settings
        |----------------------------------------------------------------------
        */

        'invoice_show_logo',
        'invoice_show_company_name',
        'invoice_show_branch_address',
        'invoice_show_token_number',
        'invoice_show_table_number',

        /*
        |----------------------------------------------------------------------
        | Currency
        |----------------------------------------------------------------------
        */

        'invoice_currency',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'landing_page_settings' => 'array',

            'vat_rate' =>
                'decimal:2',

            'service_charge_rate' =>
                'decimal:2',

            'invoice_show_logo' =>
                'boolean',

            'invoice_show_company_name' =>
                'boolean',

            'invoice_show_branch_address' =>
                'boolean',

            'invoice_show_token_number' =>
                'boolean',

            'invoice_show_table_number' =>
                'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */

    protected $attributes = [
        'vat_rate' =>
            0,

        'service_charge_rate' =>
            0,

        'vat_type' =>
    'percentage',

'service_charge_type' =>
    'percentage',

        'invoice_show_logo' =>
            true,

        'invoice_show_company_name' =>
            true,

        'invoice_show_branch_address' =>
            true,

        'invoice_show_token_number' =>
            true,

        'invoice_show_table_number' =>
            true,

        'invoice_currency' =>
            'BDT',
    ];
}
