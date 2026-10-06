<?php

namespace App\Services;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncomeService
{
    /*
    |--------------------------------------------------------------------------
    | Income List
    |--------------------------------------------------------------------------
    */

    public function getIncomes(
        array $filters = []
    ): LengthAwarePaginator {

        $query =
            $this->filteredQuery(
                $filters
            )
                ->with([
                    'category',
                    'creator',
                    'updater',
                ]);


        return $query
            ->orderByDesc(
                'income_date'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(
                max(
                    1,
                    min(
                        100,
                        (int) (
                            $filters[
                                'per_page'
                            ]
                            ??
                            10
                        )
                    )
                )
            )
            ->withQueryString();
    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    public function getSummary(
        array $filters = []
    ): array {

        $query =
            $this->filteredQuery(
                $filters
            );


        $totalAmount =
            (float) (
                (clone $query)
                    ->sum(
                        'amount'
                    )
            );


        $totalRecords =
            (clone $query)
                ->count();


        $cashAmount =
            (float) (
                (clone $query)
                    ->where(
                        'payment_method',
                        'cash'
                    )
                    ->sum(
                        'amount'
                    )
            );


        $bankAmount =
            (float) (
                (clone $query)
                    ->where(
                        'payment_method',
                        'bank_transfer'
                    )
                    ->sum(
                        'amount'
                    )
            );


        $mobileWalletAmount =
            (float) (
                (clone $query)
                    ->whereIn(
                        'payment_method',
                        [
                            'bkash',
                            'nagad',
                        ]
                    )
                    ->sum(
                        'amount'
                    )
            );


        return [

            'total_records' =>
                $totalRecords,

            'total_amount' =>
                round(
                    $totalAmount,
                    2
                ),

            'cash_amount' =>
                round(
                    $cashAmount,
                    2
                ),

            'bank_amount' =>
                round(
                    $bankAmount,
                    2
                ),

            'mobile_wallet_amount' =>
                round(
                    $mobileWalletAmount,
                    2
                ),

            'from_date' =>
                $filters[
                    'from_date'
                ]
                ??
                null,

            'to_date' =>
                $filters[
                    'to_date'
                ]
                ??
                null,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create Income
    |--------------------------------------------------------------------------
    */

    public function createIncome(
        array $data,
        User $user
    ): Income {

        return DB::transaction(
            function () use (
                $data,
                $user
            ): Income {

                $category =
                    IncomeCategory::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->findOrFail(
                            (int) $data[
                                'income_category_id'
                            ]
                        );


                $income =
                    Income::create([

                        'income_category_id' =>
                            $category->id,

                        'category_name_snapshot' =>
                            $category->name,

                        'income_date' =>
                            $data[
                                'income_date'
                            ],

                        'amount' =>
                            round(
                                (float) $data[
                                    'amount'
                                ],
                                2
                            ),

                        'payment_method' =>
                            $data[
                                'payment_method'
                            ],

                        'reference' =>
                            $data[
                                'reference'
                            ]
                            ??
                            null,

                        'notes' =>
                            $data[
                                'notes'
                            ]
                            ??
                            null,

                        'source_type' =>
                            null,

                        'source_id' =>
                            null,

                        'created_by' =>
                            $user->id,

                        'updated_by' =>
                            $user->id,

                    ]);


                return $income->fresh([
                    'category',
                    'creator',
                    'updater',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Income
    |--------------------------------------------------------------------------
    */

    public function updateIncome(
        Income $income,
        array $data,
        User $user
    ): Income {

        if (
            $income->isSystemGenerated()
        ) {
            throw ValidationException::withMessages([
                'income' => [
                    'System-generated income cannot be edited here. Update the source transaction instead.',
                ],
            ]);
        }


        return DB::transaction(
            function () use (
                $income,
                $data,
                $user
            ): Income {

                $updateData = [];


                if (
                    array_key_exists(
                        'income_category_id',
                        $data
                    )
                ) {

                    $category =
                        IncomeCategory::query()
                            ->where(
                                'is_active',
                                true
                            )
                            ->findOrFail(
                                (int) $data[
                                    'income_category_id'
                                ]
                            );


                    $updateData[
                        'income_category_id'
                    ] =
                        $category->id;


                    /*
                    |--------------------------------------------------------------------------
                    | Update Snapshot Only When Category Is Intentionally Changed
                    |--------------------------------------------------------------------------
                    */

                    $updateData[
                        'category_name_snapshot'
                    ] =
                        $category->name;
                }


                foreach (
                    [
                        'income_date',
                        'payment_method',
                        'reference',
                        'notes',
                    ]
                    as $field
                ) {

                    if (
                        array_key_exists(
                            $field,
                            $data
                        )
                    ) {
                        $updateData[
                            $field
                        ] =
                            $data[
                                $field
                            ];
                    }
                }


                if (
                    array_key_exists(
                        'amount',
                        $data
                    )
                ) {
                    $updateData[
                        'amount'
                    ] =
                        round(
                            (float) $data[
                                'amount'
                            ],
                            2
                        );
                }


                $updateData[
                    'updated_by'
                ] =
                    $user->id;


                $income->update(
                    $updateData
                );


                return $income->fresh([
                    'category',
                    'creator',
                    'updater',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Income
    |--------------------------------------------------------------------------
    */

    public function deleteIncome(
        Income $income,
        User $user
    ): void {

        if (
            $income->isSystemGenerated()
        ) {
            throw ValidationException::withMessages([
                'income' => [
                    'System-generated income cannot be deleted here. Reverse the source transaction instead.',
                ],
            ]);
        }


        DB::transaction(
            function () use (
                $income,
                $user
            ): void {

                $income->updated_by =
                    $user->id;

                $income->save();

                $income->delete();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create / Restore Category
    |--------------------------------------------------------------------------
    */

    public function createOrReactivateCategory(
        array $data,
        User $user
    ): array {

        return DB::transaction(
            function () use (
                $data,
                $user
            ): array {

                $name =
                    trim(
                        (string) $data[
                            'name'
                        ]
                    );


                $category =
                    IncomeCategory::query()
                        ->withTrashed()
                        ->whereRaw(
                            'LOWER(name) = LOWER(?)',
                            [
                                $name,
                            ]
                        )
                        ->lockForUpdate()
                        ->first();


                $wasReactivated =
                    false;


                if ($category) {

                    if (
                        $category->trashed()
                    ) {
                        $category->restore();

                        $wasReactivated =
                            true;
                    }


                    if (
                        ! $category->is_active
                    ) {
                        $category->is_active =
                            true;

                        $wasReactivated =
                            true;
                    }


                    $category->name =
                        $name;

                    $category->description =
                        $data[
                            'description'
                        ]
                        ??
                        $category->description;

                    $category->updated_by =
                        $user->id;

                    $category->save();


                    return [
                        'category' =>
                            $category->fresh(),

                        'reactivated' =>
                            $wasReactivated,

                        'created' =>
                            false,
                    ];
                }


                $category =
                    IncomeCategory::create([

                        'name' =>
                            $name,

                        'description' =>
                            $data[
                                'description'
                            ]
                            ??
                            null,

                        'is_active' =>
                            true,

                        'created_by' =>
                            $user->id,

                        'updated_by' =>
                            $user->id,

                    ]);


                return [
                    'category' =>
                        $category,

                    'reactivated' =>
                        false,

                    'created' =>
                        true,
                ];
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Base Filter Query
    |--------------------------------------------------------------------------
    */

    private function filteredQuery(
        array $filters
    ): Builder {

        $query =
            Income::query()
                ->search(
                    $filters[
                        'search'
                    ]
                    ??
                    null
                )
                ->dateRange(
                    $filters[
                        'from_date'
                    ]
                    ??
                    null,

                    $filters[
                        'to_date'
                    ]
                    ??
                    null
                );


        if (
            ! empty(
                $filters[
                    'income_category_id'
                ]
                ??
                null
            )
        ) {
            $query->where(
                'income_category_id',
                (int) $filters[
                    'income_category_id'
                ]
            );
        }


        if (
            ! empty(
                $filters[
                    'payment_method'
                ]
                ??
                null
            )
        ) {
            $query->where(
                'payment_method',
                $filters[
                    'payment_method'
                ]
            );
        }


        return $query;
    }
}
