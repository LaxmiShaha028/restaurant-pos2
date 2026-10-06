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
        | Income Categories
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')
            ->create(
                'income_categories',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'name',
                        150
                    );

                    $table->text(
                        'description'
                    )->nullable();

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    /*
                    |--------------------------------------------------------------------------
                    | Future Ledger
                    |--------------------------------------------------------------------------
                    |
                    | ledger_account_id will be added when Chart of Accounts is
                    | introduced. Do not bind Income categories to a ledger table
                    | that does not exist yet.
                    |
                    */

                    $table->unsignedBigInteger(
                        'created_by'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'updated_by'
                    )->nullable();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index(
                        'is_active',
                        'income_categories_active_idx'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Other Incomes
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')
            ->create(
                'incomes',
                function (Blueprint $table): void {

                    $table->id();

                    $table->unsignedBigInteger(
                        'income_category_id'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Historical Category Snapshot
                    |--------------------------------------------------------------------------
                    |
                    | If the category is renamed later, old accounting records still
                    | preserve the category name that existed when the income was saved.
                    |
                    */

                    $table->string(
                        'category_name_snapshot',
                        150
                    );

                    $table->date(
                        'income_date'
                    );

                    $table->decimal(
                        'amount',
                        14,
                        2
                    );

                    $table->string(
                        'payment_method',
                        50
                    );

                    $table->string(
                        'reference',
                        150
                    )->nullable();

                    $table->text(
                        'notes'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Future System Source Link
                    |--------------------------------------------------------------------------
                    |
                    | Manual Other Income leaves both fields null.
                    | Future modules may create system-generated income safely.
                    |
                    */

                    $table->string(
                        'source_type',
                        50
                    )->nullable();

                    $table->unsignedBigInteger(
                        'source_id'
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
                        'income_category_id',
                        'incomes_category_idx'
                    );

                    $table->index(
                        'income_date',
                        'incomes_date_idx'
                    );

                    $table->index(
                        'payment_method',
                        'incomes_payment_method_idx'
                    );

                    $table->unique(
                        [
                            'source_type',
                            'source_id',
                        ],
                        'incomes_source_unique'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Default Other Income Categories
        |--------------------------------------------------------------------------
        */

        $now =
            now();

        DB::connection('tenant')
            ->table(
                'income_categories'
            )
            ->insert([
                [
                    'name' =>
                        'Bottle / Crate Sale',

                    'description' =>
                        'Income from selling accumulated bottles, empty crates or similar reusable containers.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
                [
                    'name' =>
                        'Waste Paper Sale',

                    'description' =>
                        'Income from selling accumulated waste paper.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
                [
                    'name' =>
                        'Carton Sale',

                    'description' =>
                        'Income from selling accumulated cartons or packaging materials.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
                [
                    'name' =>
                        'Scrap Sale',

                    'description' =>
                        'Income from selling scrap metal, plastic or other scrap materials.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
                [
                    'name' =>
                        'Used Cooking Oil Sale',

                    'description' =>
                        'Income from selling used cooking oil where legally and operationally applicable.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
                [
                    'name' =>
                        'Recyclable Waste Sale',

                    'description' =>
                        'Income from selling other recyclable restaurant waste.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
                [
                    'name' =>
                        'Miscellaneous Other Income',

                    'description' =>
                        'Other non-POS restaurant income that does not belong to another category.',

                    'is_active' =>
                        true,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,
                ],
            ]);
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'incomes'
            );

        Schema::connection('tenant')
            ->dropIfExists(
                'income_categories'
            );
    }
};
