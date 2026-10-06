<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryService
{
    /*
    |--------------------------------------------------------------------------
    | Create Or Reactivate Category
    |--------------------------------------------------------------------------
    |
    | Expense categories are master data.
    |
    | We do not create duplicate categories only because an older category was
    | made inactive/soft-deleted. Restoring the old record preserves historical
    | relationships and is safer for future accounting/ledger integration.
    |
    */

    public function createOrReactivate(
        array $data
    ): array {

        return DB::transaction(
            function () use (
                $data
            ): array {

                $name =
                    trim(
                        (string) $data['name']
                    );

                $existing =
                    ExpenseCategory::query()
                        ->withTrashed()
                        ->whereRaw(
                            'LOWER(name) = LOWER(?)',
                            [
                                $name,
                            ]
                        )
                        ->lockForUpdate()
                        ->first();

                if ($existing) {

                    $oldData =
                        $existing->toArray();

                    if (
                        $existing->trashed()
                    ) {
                        $existing->restore();
                    }

                    $existing->name =
                        $name;

                    $existing->is_active =
                        true;

                    if (
                        array_key_exists(
                            'description',
                            $data
                        )
                    ) {
                        $existing->description =
                            $data['description'];
                    }

                    $existing->save();

                    return [
                        'category' =>
                            $existing->fresh(),

                        'created' =>
                            false,

                        'old_data' =>
                            $oldData,
                    ];
                }

                $category =
                    ExpenseCategory::create([

                        'name' =>
                            $name,

                        'description' =>
                            $data['description']
                            ?? null,

                        'is_active' =>
                            true,

                    ]);

                return [
                    'category' =>
                        $category->fresh(),

                    'created' =>
                        true,

                    'old_data' =>
                        null,
                ];
            }
        );
    }
}
