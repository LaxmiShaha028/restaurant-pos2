<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemSettingResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        return [

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'id' =>
                (int) $this->id,

            'restaurant_name' =>
                $this->restaurant_name,

            'restaurant_logo' =>
                $this->fileUrl(
                    $this->restaurant_logo
                ),

            'landing_page_settings' =>
                $this->landingPageSettings(),

            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            'country' =>
                $this->country,

            'branch_address' =>
                $this->branch_address,

            /*
            |--------------------------------------------------------------------------
            | VAT
            |--------------------------------------------------------------------------
            */

            'vat_type' =>
                $this->vat_type,

            'vat_rate' =>
                (float) $this->vat_rate,

            /*
            |--------------------------------------------------------------------------
            | Service Charge
            |--------------------------------------------------------------------------
            */

            'service_charge_type' =>
                $this->service_charge_type,

            'service_charge_rate' =>
                (float)
                $this->service_charge_rate,

            /*
            |--------------------------------------------------------------------------
            | Business Verification
            |--------------------------------------------------------------------------
            */

            'owner_nid' =>
                $this->fileUrl(
                    $this->owner_nid
                ),

            'owner_trade_license' =>
                $this->fileUrl(
                    $this->owner_trade_license
                ),

            /*
            |--------------------------------------------------------------------------
            | Invoice
            |--------------------------------------------------------------------------
            */

            'invoice_subtitle' =>
                $this->invoice_subtitle,

            'bin_number' =>
                $this->bin_number,

            'invoice_show_logo' =>
                (bool)
                $this->invoice_show_logo,

            'invoice_show_company_name' =>
                (bool)
                $this->invoice_show_company_name,

            'invoice_show_branch_address' =>
                (bool)
                $this->invoice_show_branch_address,

            'invoice_show_token_number' =>
                (bool)
                $this->invoice_show_token_number,

            'invoice_show_table_number' =>
                (bool)
                $this->invoice_show_table_number,

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'invoice_currency' =>
                $this->invoice_currency,

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | File URL Helper
    |--------------------------------------------------------------------------
    */

    protected function fileUrl(
        ?string $path
    ): ?string {
        if (
            !$path
        ) {
            return null;
        }

        return asset(
            'storage/' .
            ltrim(
                $path,
                '/'
            )
        );
    }

    protected function landingPageSettings(): array
    {
        $settings = $this->landing_page_settings ?? [];
        $image = $settings['heroImage'] ?? null;

        if ($image && !str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
            $settings['heroImage'] = $this->fileUrl($image);
        }

        return $settings;
    }
}
