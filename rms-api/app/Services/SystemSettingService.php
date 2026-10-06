<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\UpdateSystemSettingRequest;

class SystemSettingService
{
    /*
    |--------------------------------------------------------------------------
    | Get System Settings
    |--------------------------------------------------------------------------
    */

    public function get(): SystemSetting
    {
        return SystemSetting::query()
            ->firstOrCreate(
                [
                    'id' => 1,
                ],
                [
                    'restaurant_name' =>
                        config(
                            'app.name',
                            'Restaurant Management System'
                        ),

                    'vat_rate' =>
                        0,

                    'vat_type' =>
                        'percentage',

                    'service_charge_rate' =>
                        0,

                    'service_charge_type' =>
                        'percentage',

                    'invoice_currency' =>
                        'BDT',

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
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Update System Settings
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateSystemSettingRequest $request
    ): SystemSetting {
        $settings =
            $this->get();

        /*
        |--------------------------------------------------------------------------
        | Normal Text Data
        |--------------------------------------------------------------------------
        */

        $data = $request->except([
            'restaurant_logo',
            'owner_nid',
            'owner_trade_license',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Restaurant Logo
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'restaurant_logo'
            )
        ) {
            $data['restaurant_logo'] =
                $this->replaceFile(
                    file:
                        $request->file(
                            'restaurant_logo'
                        ),
                    oldPath:
                        $settings->restaurant_logo,
                    directory:
                        'system-settings/logo'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Owner NID
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'owner_nid'
            )
        ) {
            $data['owner_nid'] =
                $this->replaceFile(
                    file:
                        $request->file(
                            'owner_nid'
                        ),
                    oldPath:
                        $settings->owner_nid,
                    directory:
                        'system-settings/documents/nid'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Owner Trade License
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'owner_trade_license'
            )
        ) {
            $data['owner_trade_license'] =
                $this->replaceFile(
                    file:
                        $request->file(
                            'owner_trade_license'
                        ),
                    oldPath:
                        $settings->owner_trade_license,
                    directory:
                        'system-settings/documents/trade-license'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $settings->fill(
            $data
        );

        $settings->save();

        return $settings->fresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Replace Uploaded File
    |--------------------------------------------------------------------------
    */

    protected function replaceFile(
        UploadedFile $file,
        ?string $oldPath,
        string $directory
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Delete Previous File
        |--------------------------------------------------------------------------
        */

        if (
            $oldPath &&
            Storage::disk('public')->exists(
                $oldPath
            )
        ) {
            Storage::disk('public')->delete(
                $oldPath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Store New File
        |--------------------------------------------------------------------------
        */

        return $file->store(
            $directory,
            'public'
        );
    }
}