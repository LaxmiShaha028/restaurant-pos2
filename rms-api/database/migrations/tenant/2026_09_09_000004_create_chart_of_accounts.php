<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Chart of Accounts
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')
            ->create(
                'chart_of_accounts',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'code',
                        20
                    )->unique();

                    $table->string(
                        'system_key',
                        80
                    )
                        ->nullable()
                        ->unique();

                    $table->string(
                        'name',
                        150
                    );

                    $table->string(
                        'account_type',
                        20
                    );

                    $table->string(
                        'normal_balance',
                        10
                    );

                    $table->unsignedBigInteger(
                        'parent_id'
                    )->nullable();

                    $table->boolean(
                        'is_system'
                    )->default(false);

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    $table->boolean(
                        'allow_manual_posting'
                    )->default(true);

                    $table->text(
                        'description'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'created_by'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'updated_by'
                    )->nullable();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index(
                        'account_type',
                        'coa_account_type_idx'
                    );

                    $table->index(
                        'parent_id',
                        'coa_parent_idx'
                    );

                    $table->index(
                        'is_active',
                        'coa_active_idx'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Payment Method -> Asset Account Mapping
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')
            ->create(
                'accounting_payment_mappings',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'payment_method',
                        50
                    )->unique();

                    $table->unsignedBigInteger(
                        'ledger_account_id'
                    );

                    $table->unsignedBigInteger(
                        'updated_by'
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        'ledger_account_id',
                        'accounting_payment_ledger_idx'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Category -> Ledger Account Mapping
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')
            ->table(
                'income_categories',
                function (Blueprint $table): void {

                    $table->unsignedBigInteger(
                        'ledger_account_id'
                    )
                        ->nullable()
                        ->after(
                            'is_active'
                        );

                    $table->index(
                        'ledger_account_id',
                        'income_categories_ledger_idx'
                    );
                }
            );


        Schema::connection('tenant')
            ->table(
                'expense_categories',
                function (Blueprint $table): void {

                    $table->unsignedBigInteger(
                        'ledger_account_id'
                    )
                        ->nullable()
                        ->after(
                            'is_active'
                        );

                    $table->index(
                        'ledger_account_id',
                        'expense_categories_ledger_idx'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Seed System Chart of Accounts
        |--------------------------------------------------------------------------
        */

        $now =
            now();


        $accounts = [

            // Assets
            [
                'code' => '1000',
                'system_key' => 'assets',
                'name' => 'Assets',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => null,
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Root asset account.',
            ],
            [
                'code' => '1010',
                'system_key' => 'cash',
                'name' => 'Cash in Hand',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Physical cash held by the restaurant.',
            ],
            [
                'code' => '1020',
                'system_key' => 'bank',
                'name' => 'Bank Account',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Primary bank account / bank balance.',
            ],
            [
                'code' => '1030',
                'system_key' => 'card_settlement',
                'name' => 'Card Settlement Account',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Card/POS settlement receivable or clearing balance.',
            ],
            [
                'code' => '1040',
                'system_key' => 'bkash_wallet',
                'name' => 'bKash Wallet',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'bKash merchant/business wallet balance.',
            ],
            [
                'code' => '1050',
                'system_key' => 'nagad_wallet',
                'name' => 'Nagad Wallet',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Nagad merchant/business wallet balance.',
            ],
            [
                'code' => '1060',
                'system_key' => 'mixed_payment_clearing',
                'name' => 'Mixed Payment Clearing',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Temporary asset account for mixed payments until split posting is available.',
            ],
            [
                'code' => '1070',
                'system_key' => 'other_payment_clearing',
                'name' => 'Other Payment Clearing',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Temporary asset account for other payment methods.',
            ],
            [
                'code' => '1100',
                'system_key' => 'accounts_receivable',
                'name' => 'Accounts Receivable',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Amounts owed by customers.',
            ],
            [
                'code' => '1200',
                'system_key' => 'inventory',
                'name' => 'Inventory',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Food ingredients, raw materials and stock value.',
            ],

            // Liabilities
            [
                'code' => '2000',
                'system_key' => 'liabilities',
                'name' => 'Liabilities',
                'account_type' => 'liability',
                'normal_balance' => 'credit',
                'parent_code' => null,
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Root liability account.',
            ],
            [
                'code' => '2010',
                'system_key' => 'accounts_payable',
                'name' => 'Accounts Payable',
                'account_type' => 'liability',
                'normal_balance' => 'credit',
                'parent_code' => '2000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Amounts owed to suppliers and vendors.',
            ],
            [
                'code' => '2100',
                'system_key' => 'tax_payable',
                'name' => 'Tax Payable',
                'account_type' => 'liability',
                'normal_balance' => 'credit',
                'parent_code' => '2000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Taxes collected or accrued and payable.',
            ],

            // Equity
            [
                'code' => '3000',
                'system_key' => 'equity',
                'name' => 'Equity',
                'account_type' => 'equity',
                'normal_balance' => 'credit',
                'parent_code' => null,
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Root equity account.',
            ],
            [
                'code' => '3010',
                'system_key' => 'owners_equity',
                'name' => "Owner's Equity",
                'account_type' => 'equity',
                'normal_balance' => 'credit',
                'parent_code' => '3000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Owner capital and equity contributions.',
            ],
            [
                'code' => '3020',
                'system_key' => 'retained_earnings',
                'name' => 'Retained Earnings',
                'account_type' => 'equity',
                'normal_balance' => 'credit',
                'parent_code' => '3000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Accumulated retained profit/loss.',
            ],

            // Revenue
            [
                'code' => '4000',
                'system_key' => 'revenue',
                'name' => 'Revenue',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => null,
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Root revenue account.',
            ],
            [
                'code' => '4010',
                'system_key' => 'restaurant_sales_revenue',
                'name' => 'Restaurant Sales Revenue',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Normal restaurant/POS food and beverage sales.',
            ],
            [
                'code' => '4100',
                'system_key' => 'other_income',
                'name' => 'Other Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Parent account for non-POS other income.',
            ],
            [
                'code' => '4110',
                'system_key' => 'bottle_crate_income',
                'name' => 'Bottle / Crate Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Income from bottle and crate sales.',
            ],
            [
                'code' => '4120',
                'system_key' => 'waste_paper_income',
                'name' => 'Waste Paper Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Income from waste paper sales.',
            ],
            [
                'code' => '4130',
                'system_key' => 'carton_income',
                'name' => 'Carton Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Income from carton sales.',
            ],
            [
                'code' => '4140',
                'system_key' => 'scrap_income',
                'name' => 'Scrap Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Income from scrap sales.',
            ],
            [
                'code' => '4150',
                'system_key' => 'used_oil_income',
                'name' => 'Used Cooking Oil Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Income from selling used cooking oil.',
            ],
            [
                'code' => '4160',
                'system_key' => 'recyclable_waste_income',
                'name' => 'Recyclable Waste Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Income from other recyclable waste.',
            ],
            [
                'code' => '4190',
                'system_key' => 'misc_other_income',
                'name' => 'Miscellaneous Other Income',
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4100',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Miscellaneous non-POS income.',
            ],

            // COGS
            [
                'code' => '5000',
                'system_key' => 'cogs',
                'name' => 'Cost of Goods Sold',
                'account_type' => 'cogs',
                'normal_balance' => 'debit',
                'parent_code' => null,
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Root cost of goods sold account.',
            ],
            [
                'code' => '5010',
                'system_key' => 'food_ingredient_cogs',
                'name' => 'Food & Ingredient COGS',
                'account_type' => 'cogs',
                'normal_balance' => 'debit',
                'parent_code' => '5000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Consumed food ingredient and recipe cost.',
            ],

            // Expenses
            [
                'code' => '6000',
                'system_key' => 'operating_expenses',
                'name' => 'Operating Expenses',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => null,
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => false,
                'description' => 'Root operating expense account.',
            ],
            [
                'code' => '6100',
                'system_key' => 'salary_wages_expense',
                'name' => 'Salary & Wages Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Employee salary and wage expense.',
            ],
            [
                'code' => '6200',
                'system_key' => 'rent_expense',
                'name' => 'Rent Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Restaurant premises rent expense.',
            ],
            [
                'code' => '6300',
                'system_key' => 'utilities_expense',
                'name' => 'Utilities Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Electricity, gas, water and utility expenses.',
            ],
            [
                'code' => '6400',
                'system_key' => 'maintenance_repairs_expense',
                'name' => 'Maintenance & Repairs Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Maintenance and repair costs.',
            ],
            [
                'code' => '6500',
                'system_key' => 'marketing_expense',
                'name' => 'Marketing Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Advertising and marketing expense.',
            ],
            [
                'code' => '6600',
                'system_key' => 'delivery_transport_expense',
                'name' => 'Delivery / Transport Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Delivery, courier and transport expense.',
            ],
            [
                'code' => '6700',
                'system_key' => 'cleaning_supplies_expense',
                'name' => 'Cleaning & Supplies Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Cleaning materials and operating supplies.',
            ],
            [
                'code' => '6900',
                'system_key' => 'misc_operating_expense',
                'name' => 'Miscellaneous Operating Expense',
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '6000',
                'is_system' => true,
                'is_active' => true,
                'allow_manual_posting' => true,
                'description' => 'Other operating expenses not mapped elsewhere.',
            ],
        ];


        $idByCode = [];


        foreach ($accounts as $account) {

            $parentCode =
                $account[
                    'parent_code'
                ];


            unset(
                $account[
                    'parent_code'
                ]
            );


            $account[
                'parent_id'
            ] =
                $parentCode !== null
                    ? (
                        $idByCode[
                            $parentCode
                        ]
                        ??
                        null
                    )
                    : null;


            $account[
                'created_at'
            ] =
                $now;

            $account[
                'updated_at'
            ] =
                $now;


            $id =
                DB::connection('tenant')
                    ->table(
                        'chart_of_accounts'
                    )
                    ->insertGetId(
                        $account
                    );


            $idByCode[
                $account[
                    'code'
                ]
            ] =
                $id;
        }


        /*
        |--------------------------------------------------------------------------
        | Seed Payment Mappings
        |--------------------------------------------------------------------------
        */

        $paymentMappings = [

            'cash' =>
                '1010',

            'bank_transfer' =>
                '1020',

            'card' =>
                '1030',

            'bkash' =>
                '1040',

            'nagad' =>
                '1050',

            'mixed' =>
                '1060',

            'other' =>
                '1070',

        ];


        foreach (
            $paymentMappings
            as $paymentMethod =>
                $accountCode
        ) {

            DB::connection('tenant')
                ->table(
                    'accounting_payment_mappings'
                )
                ->insert([

                    'payment_method' =>
                        $paymentMethod,

                    'ledger_account_id' =>
                        $idByCode[
                            $accountCode
                        ],

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Seed Known Other Income Category Mappings
        |--------------------------------------------------------------------------
        */

        $incomeMappings = [

            'Bottle / Crate Sale' =>
                '4110',

            'Waste Paper Sale' =>
                '4120',

            'Carton Sale' =>
                '4130',

            'Scrap Sale' =>
                '4140',

            'Used Cooking Oil Sale' =>
                '4150',

            'Recyclable Waste Sale' =>
                '4160',

            'Miscellaneous Other Income' =>
                '4190',

        ];


        foreach (
            $incomeMappings
            as $categoryName =>
                $accountCode
        ) {

            DB::connection('tenant')
                ->table(
                    'income_categories'
                )
                ->whereRaw(
                    'LOWER(name) = LOWER(?)',
                    [
                        $categoryName,
                    ]
                )
                ->update([

                    'ledger_account_id' =>
                        $idByCode[
                            $accountCode
                        ],

                    'updated_at' =>
                        $now,

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Seed Only Safe / Exact Expense Mappings
        |--------------------------------------------------------------------------
        |
        | Unknown existing Expense categories remain UNMAPPED on purpose.
        | Admin/Manager must map them from the Chart of Accounts page before
        | Journal posting is enabled in the next phase.
        |
        */

        $expenseMappings = [

            'Salary & Wages' =>
                '6100',

            'Salary & Wages Expense' =>
                '6100',

            'Rent' =>
                '6200',

            'Rent Expense' =>
                '6200',

            'Utilities' =>
                '6300',

            'Utilities Expense' =>
                '6300',

            'Maintenance' =>
                '6400',

            'Maintenance & Repairs' =>
                '6400',

            'Marketing' =>
                '6500',

            'Marketing Expense' =>
                '6500',

        ];


        foreach (
            $expenseMappings
            as $categoryName =>
                $accountCode
        ) {

            DB::connection('tenant')
                ->table(
                    'expense_categories'
                )
                ->whereRaw(
                    'LOWER(name) = LOWER(?)',
                    [
                        $categoryName,
                    ]
                )
                ->update([

                    'ledger_account_id' =>
                        $idByCode[
                            $accountCode
                        ],

                    'updated_at' =>
                        $now,

                ]);
        }
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'expense_categories',
                function (Blueprint $table): void {

                    $table->dropIndex(
                        'expense_categories_ledger_idx'
                    );

                    $table->dropColumn(
                        'ledger_account_id'
                    );
                }
            );


        Schema::connection('tenant')
            ->table(
                'income_categories',
                function (Blueprint $table): void {

                    $table->dropIndex(
                        'income_categories_ledger_idx'
                    );

                    $table->dropColumn(
                        'ledger_account_id'
                    );
                }
            );


        Schema::connection('tenant')
            ->dropIfExists(
                'accounting_payment_mappings'
            );


        Schema::connection('tenant')
            ->dropIfExists(
                'chart_of_accounts'
            );
    }
};
