<?php

namespace App\Services\Tenant;

use App\Models\Central\Restaurant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantConnectionManager
{
    private const CONNECTION = 'tenant';

    /*
    |--------------------------------------------------------------------------
    | Previous Application State
    |--------------------------------------------------------------------------
    |
    | Request শুরু হওয়ার আগে যে default connection এবং tenant connection
    | configuration ছিল, request শেষে সেটাই restore করা হবে।
    |
    */

    private ?string $previousDefaultConnection = null;

    private ?array $previousTenantConnectionConfig = null;

    /*
    |--------------------------------------------------------------------------
    | Connection State
    |--------------------------------------------------------------------------
    */

    private bool $tenantConnected = false;

    /*
    |--------------------------------------------------------------------------
    | Connect Restaurant
    |--------------------------------------------------------------------------
    */

    public function connect(
        Restaurant|int $restaurant
    ): Restaurant {
        /*
        |--------------------------------------------------------------------------
        | Resolve Restaurant
        |--------------------------------------------------------------------------
        */

        if (is_int($restaurant)) {

            $restaurant =
                Restaurant::query()
                    ->with('tenantDatabase')
                    ->findOrFail(
                        $restaurant
                    );

        } else {

            $restaurant->loadMissing(
                'tenantDatabase'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tenant Database Configuration
        |--------------------------------------------------------------------------
        */

        $tenantDatabase =
            $restaurant->tenantDatabase;

        if (! $tenantDatabase) {

            throw new RuntimeException(
                'Tenant database configuration was not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Database Name
        |--------------------------------------------------------------------------
        */

        $databaseName =
            trim(
                (string) (
                    $tenantDatabase->database_name
                    ?? ''
                )
            );

        if ($databaseName === '') {

            throw new RuntimeException(
                'Tenant database name is missing.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Database Host
        |--------------------------------------------------------------------------
        */

        $databaseHost =
            trim(
                (string) (
                    $tenantDatabase->database_host
                    ?? ''
                )
            );

        if ($databaseHost === '') {

            throw new RuntimeException(
                'Tenant database host is missing.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Database Username
        |--------------------------------------------------------------------------
        */

        $databaseUsername =
            trim(
                (string) (
                    $tenantDatabase->database_username
                    ?? ''
                )
            );

        if ($databaseUsername === '') {

            throw new RuntimeException(
                'Tenant database username is missing.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Original Application State
        |--------------------------------------------------------------------------
        |
        | Only save this once during a connection lifecycle.
        |
        */

        if (! $this->tenantConnected) {

            $this->previousDefaultConnection =
                config('database.default');

            $this->previousTenantConnectionConfig =
                config(
                    'database.connections.' .
                    self::CONNECTION
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Build Runtime Tenant Connection
        |--------------------------------------------------------------------------
        */

        $connectionConfig = [

            'driver' =>
                'mysql',

            'host' =>
                $databaseHost,

            'port' =>
                (int) (
                    $tenantDatabase
                        ->database_port
                    ??
                    3306
                ),

            'database' =>
                $databaseName,

            'username' =>
                $databaseUsername,

            'password' =>
                (string) (
                    $tenantDatabase
                        ->database_password
                    ??
                    ''
                ),

            'unix_socket' =>
                env(
                    'DB_SOCKET',
                    ''
                ),

            'charset' =>
                'utf8mb4',

            'collation' =>
                'utf8mb4_unicode_ci',

            'prefix' =>
                '',

            'prefix_indexes' =>
                true,

            'strict' =>
                true,

            'engine' =>
                null,

            'options' =>
                extension_loaded(
                    'pdo_mysql'
                )
                    ? array_filter([
                        \PDO::MYSQL_ATTR_SSL_CA =>
                            env(
                                'MYSQL_ATTR_SSL_CA'
                            ),
                    ])
                    : [],
        ];

        /*
        |--------------------------------------------------------------------------
        | Apply Runtime Configuration
        |--------------------------------------------------------------------------
        */

        config([
            'database.connections.' .
            self::CONNECTION =>
                $connectionConfig,

            'database.default' =>
                self::CONNECTION,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove Old Tenant Connection
        |--------------------------------------------------------------------------
        */

        DB::purge(
            self::CONNECTION
        );

        /*
        |--------------------------------------------------------------------------
        | Reconnect Correct Tenant
        |--------------------------------------------------------------------------
        */

        DB::reconnect(
            self::CONNECTION
        );

        /*
        |--------------------------------------------------------------------------
        | Connectivity Test
        |--------------------------------------------------------------------------
        */

        DB::connection(
            self::CONNECTION
        )->getPdo();

        /*
        |--------------------------------------------------------------------------
        | Verify Correct Database
        |--------------------------------------------------------------------------
        */

        $connectedDatabase =
            DB::connection(
                self::CONNECTION
            )->getDatabaseName();

        if (
            $connectedDatabase
            !==
            $databaseName
        ) {

            /*
            |--------------------------------------------------------------------------
            | Safety Cleanup
            |--------------------------------------------------------------------------
            */

            DB::disconnect(
                self::CONNECTION
            );

            DB::purge(
                self::CONNECTION
            );

            if (
                $this->previousTenantConnectionConfig
                !==
                null
            ) {

                config([
                    'database.connections.' .
                    self::CONNECTION =>
                        $this->previousTenantConnectionConfig,
                ]);
            }

            if (
                $this->previousDefaultConnection
                !==
                null
            ) {

                config([
                    'database.default' =>
                        $this->previousDefaultConnection,
                ]);
            }

            $this->tenantConnected =
                false;

            throw new RuntimeException(
                sprintf(
                    'Tenant database mismatch. Expected "%s", connected to "%s".',
                    $databaseName,
                    $connectedDatabase ?? 'null'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Tenant Connection Active
        |--------------------------------------------------------------------------
        */

        $this->tenantConnected =
            true;

        return $restaurant;
    }

    /*
    |--------------------------------------------------------------------------
    | Disconnect Tenant
    |--------------------------------------------------------------------------
    */

    public function disconnect(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Disconnect Current Tenant Connection
        |--------------------------------------------------------------------------
        */

        DB::disconnect(
            self::CONNECTION
        );

        DB::purge(
            self::CONNECTION
        );

        /*
        |--------------------------------------------------------------------------
        | Restore Previous Tenant Connection Configuration
        |--------------------------------------------------------------------------
        */

        if (
            $this->previousTenantConnectionConfig
            !==
            null
        ) {

            config([
                'database.connections.' .
                self::CONNECTION =>
                    $this->previousTenantConnectionConfig,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Restore Previous Default Connection
        |--------------------------------------------------------------------------
        */

        if (
            $this->previousDefaultConnection
            !==
            null
        ) {

            config([
                'database.default' =>
                    $this->previousDefaultConnection,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reset Manager State
        |--------------------------------------------------------------------------
        */

        $this->previousTenantConnectionConfig =
            null;

        $this->previousDefaultConnection =
            null;

        $this->tenantConnected =
            false;
    }

    /*
    |--------------------------------------------------------------------------
    | Database Name
    |--------------------------------------------------------------------------
    */

    public function databaseName(): ?string
    {
        $config =
            config(
                'database.connections.' .
                self::CONNECTION
            );

        if (
            ! is_array($config)
        ) {
            return null;
        }

        return $config['database']
            ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Connection Name
    |--------------------------------------------------------------------------
    */

    public function connectionName(): string
    {
        return self::CONNECTION;
    }

    /*
    |--------------------------------------------------------------------------
    | Is Connected
    |--------------------------------------------------------------------------
    */

    public function isConnected(): bool
    {
        return $this->tenantConnected;
    }
}
