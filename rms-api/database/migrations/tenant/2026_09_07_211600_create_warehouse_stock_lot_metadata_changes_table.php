<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    private const TABLE =
        'warehouse_stock_lot_metadata_changes';

    private const LOT_FOREIGN_KEY =
        'wsl_meta_lot_fk';

    private const USER_FOREIGN_KEY =
        'wsl_meta_user_fk';

    private const LOT_DATE_INDEX =
        'wsl_meta_lot_date_idx';


    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Create Base Table Safely
        |--------------------------------------------------------------------------
        |
        | The previous version could partially create this table before MySQL
        | rejected Laravel's auto-generated foreign-key name for being >64 chars.
        |
        | Therefore this migration MUST be safe in both cases:
        |
        | 1. Table does not exist yet
        | 2. Table already exists partially from the failed migration
        |
        */

        if (
            !Schema::connection('tenant')
                ->hasTable(
                    self::TABLE
                )
        ) {

            Schema::connection('tenant')
                ->create(
                    self::TABLE,
                    function (Blueprint $table): void {

                        $table->id();

                        /*
                        |--------------------------------------------------------------------------
                        | Use unsignedBigInteger instead of ->constrained()
                        |--------------------------------------------------------------------------
                        |
                        | Foreign keys are added below with explicit SHORT names.
                        |
                        */

                        $table->unsignedBigInteger(
                            'warehouse_stock_lot_id'
                        );

                        $table->json(
                            'changed_fields'
                        );

                        $table->json(
                            'before_snapshot'
                        );

                        $table->json(
                            'after_snapshot'
                        );

                        $table->string(
                            'change_reason',
                            1000
                        );

                        $table->unsignedBigInteger(
                            'created_by'
                        )->nullable();

                        $table->timestamps();

                        $table->index(
                            [
                                'warehouse_stock_lot_id',
                                'created_at',
                            ],
                            self::LOT_DATE_INDEX
                        );
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Repair / Add Lot Foreign Key
        |--------------------------------------------------------------------------
        |
        | Explicit name stays safely below MySQL's 64-character identifier limit.
        |
        */

        if (
            !$this->constraintExists(
                self::LOT_FOREIGN_KEY
            )
        ) {

            Schema::connection('tenant')
                ->table(
                    self::TABLE,
                    function (Blueprint $table): void {

                        $table->foreign(
                            'warehouse_stock_lot_id',
                            self::LOT_FOREIGN_KEY
                        )
                            ->references(
                                'id'
                            )
                            ->on(
                                'warehouse_stock_lots'
                            )
                            ->restrictOnDelete();
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Repair / Add User Foreign Key
        |--------------------------------------------------------------------------
        */

        if (
            !$this->constraintExists(
                self::USER_FOREIGN_KEY
            )
        ) {

            Schema::connection('tenant')
                ->table(
                    self::TABLE,
                    function (Blueprint $table): void {

                        $table->foreign(
                            'created_by',
                            self::USER_FOREIGN_KEY
                        )
                            ->references(
                                'id'
                            )
                            ->on(
                                'users'
                            )
                            ->nullOnDelete();
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Repair Index If Failed Migration Left It Missing
        |--------------------------------------------------------------------------
        */

        if (
            !$this->indexExists(
                self::LOT_DATE_INDEX
            )
        ) {

            Schema::connection('tenant')
                ->table(
                    self::TABLE,
                    function (Blueprint $table): void {

                        $table->index(
                            [
                                'warehouse_stock_lot_id',
                                'created_at',
                            ],
                            self::LOT_DATE_INDEX
                        );
                    }
                );
        }
    }


    public function down(): void
    {
        if (
            !Schema::connection('tenant')
                ->hasTable(
                    self::TABLE
                )
        ) {
            return;
        }


        Schema::connection('tenant')
            ->table(
                self::TABLE,
                function (Blueprint $table): void {

                    if (
                        $this->constraintExists(
                            self::LOT_FOREIGN_KEY
                        )
                    ) {

                        $table->dropForeign(
                            self::LOT_FOREIGN_KEY
                        );
                    }


                    if (
                        $this->constraintExists(
                            self::USER_FOREIGN_KEY
                        )
                    ) {

                        $table->dropForeign(
                            self::USER_FOREIGN_KEY
                        );
                    }
                }
            );


        Schema::connection('tenant')
            ->dropIfExists(
                self::TABLE
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MySQL Metadata Helpers
    |--------------------------------------------------------------------------
    */

    private function constraintExists(
        string $constraintName
    ): bool {

        $database =
            DB::connection(
                'tenant'
            )
                ->getDatabaseName();


        $result =
            DB::connection(
                'tenant'
            )
                ->selectOne(
                    '
                        SELECT COUNT(*) AS aggregate
                        FROM information_schema.TABLE_CONSTRAINTS
                        WHERE CONSTRAINT_SCHEMA = ?
                          AND TABLE_NAME = ?
                          AND CONSTRAINT_NAME = ?
                    ',
                    [
                        $database,
                        self::TABLE,
                        $constraintName,
                    ]
                );


        return
            (int) (
                $result->aggregate
                ??
                0
            )
            >
            0;
    }


    private function indexExists(
        string $indexName
    ): bool {

        $database =
            DB::connection(
                'tenant'
            )
                ->getDatabaseName();


        $result =
            DB::connection(
                'tenant'
            )
                ->selectOne(
                    '
                        SELECT COUNT(*) AS aggregate
                        FROM information_schema.STATISTICS
                        WHERE TABLE_SCHEMA = ?
                          AND TABLE_NAME = ?
                          AND INDEX_NAME = ?
                    ',
                    [
                        $database,
                        self::TABLE,
                        $indexName,
                    ]
                );


        return
            (int) (
                $result->aggregate
                ??
                0
            )
            >
            0;
    }
};
