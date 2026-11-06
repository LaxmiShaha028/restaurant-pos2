<?php

namespace App\Services\Tenant;

use App\Models\Central\Domain;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use App\Models\Central\TenantDatabase;
use Database\Seeders\Tenant\TenantDatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;


class TenantProvisioningService
{
    private const TENANT_CONNECTION = 'tenant';

    private const DATABASE_CONNECTION = 'mysql';

    public function createPendingRestaurant(
    array $data
): array {
    /*
    |--------------------------------------------------------------------------
    | Required Data
    |--------------------------------------------------------------------------
    */

    $restaurantName =
        trim(
            (string) (
                $data['name']
                ?? ''
            )
        );

    $ownerName =
        trim(
            (string) (
                $data['owner_name']
                ?? ''
            )
        );

    $ownerEmail =
        strtolower(
            trim(
                (string) (
                    $data['owner_email']
                    ?? ''
                )
            )
        );

    $username =
        trim(
            (string) (
                $data['username']
                ?? ''
            )
        );

    $subscriptionPlanId =
        (int) (
            $data['subscription_plan_id']
            ?? 0
        );

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $restaurantName === ''
    ) {
        throw new RuntimeException(
            'Restaurant name is required.'
        );
    }

    if (
        $ownerName === ''
    ) {
        throw new RuntimeException(
            'Owner name is required.'
        );
    }

    if (
        $ownerEmail === ''
    ) {
        throw new RuntimeException(
            'Owner email is required.'
        );
    }

    if (
        $username === ''
    ) {
        throw new RuntimeException(
            'Tenant admin username is required.'
        );
    }

    if (
        $subscriptionPlanId <= 0
    ) {
        throw new RuntimeException(
            'Subscription plan is required.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Central Identity
    |--------------------------------------------------------------------------
    */

    $slug =
        $this->generateUniqueSlug(
            $restaurantName
        );

    $tenantKey =
        $this->generateTenantKey(
            $slug
        );

    /*
    |--------------------------------------------------------------------------
    | Create Central Restaurant + Pending Subscription
    |--------------------------------------------------------------------------
    */

    return DB::connection('central')
        ->transaction(
            function () use (
                $data,
                $restaurantName,
                $ownerName,
                $ownerEmail,
                $username,
                $subscriptionPlanId,
                $slug,
                $tenantKey
            ): array {

                /*
                |--------------------------------------------------------------------------
                | Restaurant
                |--------------------------------------------------------------------------
                */

                $restaurant =
                    Restaurant::on('central')
                        ->create([
                            'name' =>
                                $restaurantName,

                            'slug' =>
                                $slug,

                            'logo_path' =>
                                $data['logo_path']
                                ?? null,

                            'owner_name' =>
                                $ownerName,

                            'owner_email' =>
                                $ownerEmail,

                            'phone' =>
                                $data['phone']
                                ?? null,

                            'address' =>
                                $data['address']
                                ?? null,

                            'city' =>
                                $data['city']
                                ?? null,

                            'country' =>
                                $data['country']
                                ?? null,

                            'tenant_key' =>
                                $tenantKey,

                            'status' =>
                                'pending',
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Pending Subscription
                |--------------------------------------------------------------------------
                */

                $subscription =
                    Subscription::on('central')
                        ->create([
                            'restaurant_id' =>
                                $restaurant->id,

                            'subscription_plan_id' =>
                                $subscriptionPlanId,

                            'status' =>
                                'pending',

                            'start_at' =>
                                null,

                            'end_at' =>
                                null,

                            'trial_start_at' =>
                                null,

                            'trial_end_at' =>
                                null,

                            'auto_renew' =>
                                false,

                            'cancelled_at' =>
                                null,

                            'cancel_reason' =>
                                null,
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Result
                |--------------------------------------------------------------------------
                */

                return [
                    'restaurant' =>
                        $restaurant
                            ->fresh(),

                    'subscription' =>
                        $subscription
                            ->fresh()
                            ->load('plan'),

                    'status' =>
                        'pending',

                    'payment_required' =>
                        true,
                ];
            }
        );
}
public function provisionExistingRestaurant(
        Restaurant $restaurant,
        array $data = []
    ): array {

        /*
|--------------------------------------------------------------------------
| Existing Tenant Database State
|--------------------------------------------------------------------------
*/

$restaurant->loadMissing([
    'tenantDatabase',
]);

$existingTenantDatabase =
    $restaurant->tenantDatabase;

$failedTenantDatabase = null;

/*
|--------------------------------------------------------------------------
| Already Active Protection
|--------------------------------------------------------------------------
*/

if (
    $existingTenantDatabase
    &&
    $existingTenantDatabase->status === 'active'
) {
    throw new RuntimeException(
        'Tenant database is already active for this restaurant.'
    );
}

/*
|--------------------------------------------------------------------------
| Failed Provisioning Recovery
|--------------------------------------------------------------------------
*/

if (
    $existingTenantDatabase
    &&
    $existingTenantDatabase->status === 'failed'
) {
    $failedTenantDatabase = $existingTenantDatabase;
}

        /*
        |--------------------------------------------------------------------------
        | Pending Subscription
        |--------------------------------------------------------------------------
        */

        $subscription =
            Subscription::on('central')
                ->with('plan')
                ->where(
                    'restaurant_id',
                    $restaurant->id
                )
                ->where(
                    'status',
                    'pending'
                )
                ->latest('id')
                ->first();

        if (! $subscription) {
            throw new RuntimeException(
                'Pending subscription was not found.'
            );
        }

        $plan =
    $subscription->plan;

if (! $plan) {
    throw new RuntimeException(
        'Subscription plan was not found.'
    );
}

if (
    (float) $plan->price <= 0
) {
    throw new RuntimeException(
        'This provisioning flow is only available for paid subscriptions.'
    );
}

/*
|--------------------------------------------------------------------------
| Clean Up Incomplete Shared-Database Provisioning
|--------------------------------------------------------------------------
|
| A failed migration can leave some prefixed tenant tables behind without
| recording the migration. Remove only this inactive restaurant's table
| prefix before retrying, so migrations can start from a clean schema.
|
*/

if ($failedTenantDatabase) {
    if (config('tenancy.mode') === 'single_database') {
        $this->dropTenantTables((int) $restaurant->id);
    }

    $failedTenantDatabase->delete();
    $restaurant->unsetRelation('tenantDatabase');
}

        /*
        |--------------------------------------------------------------------------
        | Generate Tenant Resources
        |--------------------------------------------------------------------------
        */

        $databaseName =
            $this->generateDatabaseName(
                $restaurant->slug
            );

        $domain =
            $this->generatePrimaryDomain(
                $restaurant->slug
            );

        /*
        |--------------------------------------------------------------------------
        | Tenant Admin
        |--------------------------------------------------------------------------
        */

        $username =
            trim(
                (string) (
                    $data['username']
                    ??
                    Str::before(
                        strtolower(
                            trim(
                                $restaurant->owner_email
                            )
                        ),
                        '@'
                    )
                )
            );

        if ($username === '') {
            $username = 'admin';
        }

$password =
    trim(
        (string) (
            $data['password']
            ?? ''
        )
    );

if (
    $password === ''
) {

    $password =
        $this->generateAdminPassword();
}

        $tenantDatabase = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Mark Restaurant As Provisioning
            |--------------------------------------------------------------------------
            */

            $restaurant->forceFill([
                'status' =>
                    'provisioning',
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Create Physical Database
            |--------------------------------------------------------------------------
            */

            $this->createDatabase(
                $databaseName
            );

            /*
            |--------------------------------------------------------------------------
            | Create Central Tenant Database Record
            |--------------------------------------------------------------------------
            */

            $tenantDatabase =
                TenantDatabase::on('central')
                    ->create([
                        'restaurant_id' =>
                            $restaurant->id,

                        'connection_name' =>
                            self::TENANT_CONNECTION,

                        'database_host' =>
                            $this->tenantDatabaseSetting('host'),

                        'database_port' =>
                            $this->tenantDatabaseSetting('port'),

                        'database_name' =>
                            $databaseName,

                        'database_username' =>
                            $this->tenantDatabaseSetting('username'),

                        'database_password' =>
                            $this->tenantDatabaseSetting('password'),

                        'status' =>
                            'provisioning',

                        'migrated_at' =>
                            null,

                        'provisioned_at' =>
                            null,

                        'last_error' =>
                            null,
                    ]);

            /*
            |--------------------------------------------------------------------------
            | Configure Tenant Connection
            |--------------------------------------------------------------------------
            */

            $this->configureTenantConnection(
                $tenantDatabase
            );

            /*
            |--------------------------------------------------------------------------
            | Run Tenant Migrations
            |--------------------------------------------------------------------------
            */

            $this->runTenantMigrations();

            /*
            |--------------------------------------------------------------------------
            | Mark Migration Complete
            |--------------------------------------------------------------------------
            */

            $tenantDatabase->forceFill([
                'status' =>
                    'active',

                'migrated_at' =>
                    now(),

                'last_error' =>
                    null,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Seed Tenant
            |--------------------------------------------------------------------------
            */

            $this->seedTenant([
                'name' =>
                    $restaurant->name,

                'logo_path' =>
                    $restaurant->logo_path,

                'owner_name' =>
                    $restaurant->owner_name,

                'owner_email' =>
                    $restaurant->owner_email,

                'address' =>
                    $restaurant->address,

                'city' =>
                    $restaurant->city,

                'country' =>
                    $restaurant->country,

                'username' =>
                    $username,

                'password' =>
                    $password,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Primary Domain
            |--------------------------------------------------------------------------
            */

            Domain::on('central')
                ->updateOrCreate(
                    [
                        'restaurant_id' =>
                            $restaurant->id,

                        'domain' =>
                            $domain,
                    ],
                    [
                        'type' =>
                            'subdomain',

                        'is_primary' =>
                            true,

                        'is_verified' =>
                            true,

                        'verification_token' =>
                            null,

                        'ssl_status' =>
                            'pending',
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | Tenant Database Provisioned
            |--------------------------------------------------------------------------
            */

            $tenantDatabase->forceFill([
                'status' =>
                    'active',

                'provisioned_at' =>
                    now(),

                'last_error' =>
                    null,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Activate Subscription
            |--------------------------------------------------------------------------
            */

            $startAt =
                now();

            $endAt =
                match (
                    $plan->billing_interval
                ) {
                    'day' =>
                        $startAt
                            ->copy()
                            ->addDays(
                                $plan->billing_interval_count
                            ),

                    'week' =>
                        $startAt
                            ->copy()
                            ->addWeeks(
                                $plan->billing_interval_count
                            ),

                    'year' =>
                        $startAt
                            ->copy()
                            ->addYears(
                                $plan->billing_interval_count
                            ),

                    default =>
                        $startAt
                            ->copy()
                            ->addMonths(
                                $plan->billing_interval_count
                            ),
                };

            $subscription->forceFill([
                'status' =>
                    'active',

                'start_at' =>
                    $startAt,

                'end_at' =>
                    $endAt,

                'trial_start_at' =>
                    null,

                'trial_end_at' =>
                    null,

                'cancelled_at' =>
                    null,

                'cancel_reason' =>
                    null,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Activate Restaurant
            |--------------------------------------------------------------------------
            */

            $restaurant->forceFill([
                'status' =>
                    'active',
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Cleanup
            |--------------------------------------------------------------------------
            */

            DB::disconnect(
                self::TENANT_CONNECTION
            );

            DB::purge(
                self::TENANT_CONNECTION
            );

            /*
            |--------------------------------------------------------------------------
            | Return Result
            |--------------------------------------------------------------------------
            */

            return [
                'restaurant' =>
                    $restaurant
                        ->fresh(),

                'tenant_database' =>
                    $tenantDatabase
                        ->fresh(),

                'subscription' =>
                    $subscription
                        ->fresh()
                        ->load('plan'),

                'domain' =>
                    $domain,

                'admin' => [
                    'username' =>
                        $username,

                    'password' =>
                        $password,
                ],
            ];

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Tenant Connection Cleanup
            |--------------------------------------------------------------------------
            */

            try {

                DB::disconnect(
                    self::TENANT_CONNECTION
                );

                DB::purge(
                    self::TENANT_CONNECTION
                );

            } catch (Throwable) {
                // Ignore cleanup failure.
            }

            /*
            |--------------------------------------------------------------------------
            | Store Tenant Database Error
            |--------------------------------------------------------------------------
            */

            if (
                $tenantDatabase
            ) {
                $tenantDatabase->forceFill([
                    'status' =>
                        'failed',

                    'last_error' =>
                        $exception->getMessage(),
                ])->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Store Restaurant Error
            |--------------------------------------------------------------------------
            */

            $restaurant->forceFill([
                'status' =>
                    'failed',
            ])->save();

            report(
                $exception
            );

            throw $exception;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Provision Restaurant
    |--------------------------------------------------------------------------
    */

    public function provision(
        array $data
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Required Data
        |--------------------------------------------------------------------------
        */

        $restaurantName =
            trim(
                (string) (
                    $data['name']
                    ?? ''
                )
            );

        $ownerName =
            trim(
                (string) (
                    $data['owner_name']
                    ?? ''
                )
            );

        $ownerEmail =
            strtolower(
                trim(
                    (string) (
                        $data['owner_email']
                        ?? ''
                    )
                )
            );

        $username =
            trim(
                (string) (
                    $data['username']
                    ?? ''
                )
            );

        if ($restaurantName === '') {
            throw new RuntimeException(
                'Restaurant name is required.'
            );
        }

        if ($ownerName === '') {
            throw new RuntimeException(
                'Owner name is required.'
            );
        }

        if ($ownerEmail === '') {
            throw new RuntimeException(
                'Owner email is required.'
            );
        }

        if ($username === '') {
            throw new RuntimeException(
                'Tenant admin username is required.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $slug =
            $this->generateUniqueSlug(
                $restaurantName
            );

        /*
        |--------------------------------------------------------------------------
        | Tenant Key
        |--------------------------------------------------------------------------
        */

        $tenantKey =
            $this->generateTenantKey(
                $slug
            );

        /*
        |--------------------------------------------------------------------------
        | Database Name
        |--------------------------------------------------------------------------
        */

        $databaseName =
            $this->generateDatabaseName(
                $slug
            );

        /*
        |--------------------------------------------------------------------------
        | Domain
        |--------------------------------------------------------------------------
        |
        | Local:
        |
        | second-restaurant.local
        |
        | Production:
        |
        | second-restaurant.yourdomain.com
        |
        */

        $domain =
            $this->generatePrimaryDomain(
                $slug
            );

        /*
        |--------------------------------------------------------------------------
        | Admin Password
        |--------------------------------------------------------------------------
        |
        | If Super Admin did not explicitly provide a password,
        | automatically generate one.
        |
        */

        $adminPassword =
            (string) (
                $data['password']
                ??
                $this->generateAdminPassword()
            );

        /*
        |--------------------------------------------------------------------------
        | Main Provisioning
        |--------------------------------------------------------------------------
        */

        $restaurant = null;

        $tenantDatabase = null;

        $subscription = null;

        $databaseCreated = false;

        try {

            /*
            |--------------------------------------------------------------------------
            | Central Transaction
            |--------------------------------------------------------------------------
            */

            DB::connection('central')
                ->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Create Restaurant
            |--------------------------------------------------------------------------
            */

            $restaurant =
                Restaurant::on('central')
                    ->create([

                        'name' =>
                            $restaurantName,

                        'slug' =>
                            $slug,

                        'logo_path' =>
                            $data['logo_path']
                            ?? null,

                        'owner_name' =>
                            $ownerName,

                        'owner_email' =>
                            $ownerEmail,

                        'phone' =>
                            $data['phone']
                            ?? null,

                        'address' =>
                            $data['address']
                            ?? null,

                        'city' =>
                            $data['city']
                            ?? null,

                        'country' =>
                            $data['country']
                            ?? null,

                        'tenant_key' =>
                            $tenantKey,

                        'status' =>
                            'provisioning',
                    ]);

            /*
            |--------------------------------------------------------------------------
            | Subscription
            |--------------------------------------------------------------------------
            |
            | subscription_id is optional during testing.
            |
            */

            if (
                ! empty(
                    $data['subscription_plan_id']
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Subscription Dates
                |--------------------------------------------------------------------------
                */

                $subscriptionStartAt =
                    now();

                $trialDays =
                    max(
                        0,
                        (int) (
                            $data['trial_days']
                            ?? 0
                        )
                    );

                $isTrial =
                    $trialDays > 0;

                $trialStartAt =
                    $isTrial
                        ? $subscriptionStartAt->copy()
                        : null;

                $trialEndAt =
                    $isTrial
                        ? $subscriptionStartAt
                            ->copy()
                            ->addDays(
                                $trialDays
                            )
                        : null;

                /*
                |--------------------------------------------------------------------------
                | Subscription
                |--------------------------------------------------------------------------
                */

                $subscription =
                    Subscription::on('central')
                        ->create([

                            'restaurant_id' =>
                                $restaurant->id,

                            'subscription_plan_id' =>
                                (int)
                                    $data[
                                        'subscription_plan_id'
                                    ],

                            'status' =>
                                $data[
                                    'subscription_status'
                                ]
                                ??
                                'active',

                            'start_at' =>
                                $subscriptionStartAt,

                            /*
                             * Trial uses the trial end date as the
                             * subscription end date.
                             *
                             * Paid pending subscriptions keep end_at NULL
                             * until payment is completed.
                             */
                            'end_at' =>
                                $isTrial
                                    ? $trialEndAt
                                    : (
                                        $data['end_at']
                                        ?? null
                                    ),

                            'trial_start_at' =>
                                $trialStartAt,

                            'trial_end_at' =>
                                $trialEndAt,

                            'auto_renew' =>
                                (bool) (
                                    $data[
                                        'auto_renew'
                                    ]
                                    ?? false
                                ),
                        ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Commit Central Transaction
            |--------------------------------------------------------------------------
            |
            | Database creation cannot participate in normal application-level
            | database transaction safely, so central record is committed first.
            |
            */

            DB::connection('central')
                ->commit();

            /*
            |--------------------------------------------------------------------------
            | Create Physical Database
            |--------------------------------------------------------------------------
            */

            $this->createDatabase(
                $databaseName
            );

            $databaseCreated = true;

            /*
            |--------------------------------------------------------------------------
            | Tenant Database Record
            |--------------------------------------------------------------------------
            */

            $tenantDatabase =
                TenantDatabase::on('central')
                    ->create([

                        'restaurant_id' =>
                            $restaurant->id,

                        'connection_name' =>
                            self::TENANT_CONNECTION,

                        'database_host' =>
                            $this->tenantDatabaseSetting('host'),

                        'database_port' =>
                            $this->tenantDatabaseSetting('port'),

                        'database_name' =>
                            $databaseName,

                        'database_username' =>
                            $this->tenantDatabaseSetting('username'),

                        'database_password' =>
                            $this->tenantDatabaseSetting('password'),

                        'status' =>
                            'provisioning',

                        'migrated_at' =>
                            null,

                        'provisioned_at' =>
                            null,

                        'last_error' =>
                            null,
                    ]);

            /*
            |--------------------------------------------------------------------------
            | Configure Tenant Connection
            |--------------------------------------------------------------------------
            */

            $this->configureTenantConnection(
                $tenantDatabase
            );

            /*
            |--------------------------------------------------------------------------
            | Run Tenant Migrations
            |--------------------------------------------------------------------------
            */

            $this->runTenantMigrations();

            $tenantDatabase->forceFill([
                'migrated_at' =>
                    now(),

                'status' =>
                    'active',

                'last_error' =>
                    null,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Seed Tenant
            |--------------------------------------------------------------------------
            */

            $this->seedTenant([
                'name' =>
                    $restaurant->name,

                'logo_path' =>
                    $restaurant->logo_path,

                'owner_name' =>
                    $restaurant->owner_name,

                'owner_email' =>
                    $restaurant->owner_email,

                'address' =>
                    $restaurant->address,

                'city' =>
                    $restaurant->city,

                'country' =>
                    $restaurant->country,

                'username' =>
                    $username,

                'password' =>
                    $adminPassword,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Primary Domain
            |--------------------------------------------------------------------------
            */

            Domain::on('central')
                ->create([

                    'restaurant_id' =>
                        $restaurant->id,

                    'domain' =>
                        $domain,

                    'type' =>
                        'subdomain',

                    'is_primary' =>
                        true,

                    /*
                    | Local development domains do not need external DNS
                    | verification.
                    |
                    */

                    'is_verified' =>
                        true,

                    'verification_token' =>
                        null,

                    'ssl_status' =>
                        'pending',
                ]);

            /*
            |--------------------------------------------------------------------------
            | Mark Tenant Provisioned
            |--------------------------------------------------------------------------
            */

            $tenantDatabase->forceFill([
                'status' =>
                    'active',

                'provisioned_at' =>
                    now(),

                'last_error' =>
                    null,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Activate Restaurant
            |--------------------------------------------------------------------------
            */

            $restaurant->forceFill([
                'status' =>
                    'active',
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Disconnect Tenant
            |--------------------------------------------------------------------------
            */

            DB::disconnect(
                self::TENANT_CONNECTION
            );

            DB::purge(
                self::TENANT_CONNECTION
            );

            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */

            return [

                'restaurant' =>
                    $restaurant
                        ->fresh(),

                'tenant_database' =>
                    $tenantDatabase
                        ->fresh(),

                'subscription' =>
                    $subscription,

                'domain' =>
                    $domain,

                'admin' => [

                    'username' =>
                        $username,

                    /*
                    | IMPORTANT:
                    | This is returned only once during provisioning.
                    */

                    'password' =>
                        $adminPassword,
                ],
            ];

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Rollback Central Transaction
            |--------------------------------------------------------------------------
            */

            if (
                DB::connection('central')
                    ->transactionLevel() > 0
            ) {
                DB::connection('central')
                    ->rollBack();
            }

            /*
            |--------------------------------------------------------------------------
            | Tenant Connection Cleanup
            |--------------------------------------------------------------------------
            */

            try {

                DB::disconnect(
                    self::TENANT_CONNECTION
                );

                DB::purge(
                    self::TENANT_CONNECTION
                );

            } catch (Throwable) {
                // Ignore cleanup failure.
            }

            /*
            |--------------------------------------------------------------------------
            | Store Provisioning Error
            |--------------------------------------------------------------------------
            */

            if (
                $tenantDatabase
            ) {

                $tenantDatabase->forceFill([

                    'status' =>
                        'failed',

                    'last_error' =>
                        $exception->getMessage(),

                ])->save();
            }

            if (
                $restaurant
                &&
                $restaurant->exists
            ) {

                $restaurant->forceFill([
                    'status' =>
                        'failed',
                ])->save();
            }

            report(
                $exception
            );

            throw $exception;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create Database
    |--------------------------------------------------------------------------
    */

    private function tenantDatabaseSetting(string $key): mixed
    {
        if (
            config('tenancy.mode') === 'single_database'
            && $key === 'password'
        ) {
            return null;
        }

        $connection = config('tenancy.mode') === 'single_database'
            ? 'central'
            : self::DATABASE_CONNECTION;

        return config("database.connections.{$connection}.{$key}");
    }

    private function createDatabase(
        string $databaseName
    ): void {

        if (config('tenancy.mode') === 'single_database') {
            return;
        }

        $connection =
            DB::connection(
                self::DATABASE_CONNECTION
            );

        $safeName =
            str_replace(
                '`',
                '``',
                $databaseName
            );

        $connection->statement(
            "CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }

    private function dropTenantTables(int $restaurantId): void
    {
        $connection = DB::connection('central');
        $databaseName = (string) config('database.connections.central.database');
        $prefix = 't' . $restaurantId . '_';

        $tables = $connection->select(
            'SELECT TABLE_NAME AS table_name
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ?
               AND LEFT(TABLE_NAME, CHAR_LENGTH(?)) = ?',
            [$databaseName, $prefix, $prefix]
        );

        if ($tables === []) {
            return;
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                $tableName = str_replace('`', '``', (string) $table->table_name);
                $connection->statement("DROP TABLE IF EXISTS `{$tableName}`");
            }
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Configure Tenant Connection
    |--------------------------------------------------------------------------
    */

    private function configureTenantConnection(
        TenantDatabase $tenantDatabase
    ): void {

        $singleDatabaseMode =
            config('tenancy.mode') === 'single_database';

        $centralConfig =
            config('database.connections.central', []);

        config([
            'database.connections.tenant' => [

                'driver' =>
                    'mysql',

                'host' => $singleDatabaseMode
                    ? $centralConfig['host']
                    : $tenantDatabase->database_host,

                'port' => $singleDatabaseMode
                    ? $centralConfig['port']
                    : $tenantDatabase->database_port,

                'database' => $singleDatabaseMode
                    ? $centralConfig['database']
                    : $tenantDatabase->database_name,

                'username' => $singleDatabaseMode
                    ? $centralConfig['username']
                    : $tenantDatabase->database_username,

                'password' => $singleDatabaseMode
                    ? $centralConfig['password']
                    : $tenantDatabase->database_password,

                'unix_socket' =>
                    env(
                        'DB_SOCKET',
                        ''
                    ),

                'charset' =>
                    'utf8mb4',

                'collation' =>
                    'utf8mb4_unicode_ci',

                'prefix' => $singleDatabaseMode
                    ? 't' . (int) $tenantDatabase->restaurant_id . '_'
                    : '',

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
            ],
        ]);

        DB::purge(
            self::TENANT_CONNECTION
        );

        DB::reconnect(
            self::TENANT_CONNECTION
        );

        DB::setDefaultConnection(
            self::TENANT_CONNECTION
        );

        DB::connection(
            self::TENANT_CONNECTION
        )->getPdo();
    }

    /*
    |--------------------------------------------------------------------------
    | Run Tenant Migrations
    |--------------------------------------------------------------------------
    */

    private function runTenantMigrations(): void
    {
        $exitCode =
            Artisan::call(
                'migrate',
                [

                    '--database' =>
                        self::TENANT_CONNECTION,

                    '--path' =>
                        'database/migrations/tenant',

                    '--force' =>
                        true,

                ]
            );

        if (
            $exitCode !== 0
        ) {

            throw new RuntimeException(
                "Tenant migration failed.\n\n"
                .
                Artisan::output()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Seed Tenant
    |--------------------------------------------------------------------------
    */

    private function seedTenant(
        array $restaurant
    ): void {

        app(
            TenantDatabaseSeeder::class
        )->run(
            $restaurant
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $restaurantName
    ): string {

        $base =
            Str::slug(
                $restaurantName
            );

        if (
            $base === ''
        ) {
            $base =
                'restaurant';
        }

        $slug =
            $base;

        $counter = 1;

        while (
            Restaurant::on('central')
                ->where('slug', $slug)
                ->exists()
        ) {

            $counter++;

            $slug =
                $base
                . '-'
                . $counter;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Tenant Key
    |--------------------------------------------------------------------------
    */

    private function generateTenantKey(
        string $slug
    ): string {

        return $slug
            . '-'
            . Str::lower(
                Str::random(8)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Database Name
    |--------------------------------------------------------------------------
    */

    private function generateDatabaseName(
    string $slug
): string {
    if (config('tenancy.mode') === 'single_database') {
        return (string) config('database.connections.central.database');
    }

    $base =
        'tenant_'
        .
        Str::slug(
            $slug,
            '_'
        );

    $databaseName =
        $base;

    $counter = 1;

    while (
        DB::connection(
            self::DATABASE_CONNECTION
        )->selectOne(
            '
            SELECT SCHEMA_NAME
            FROM information_schema.schemata
            WHERE SCHEMA_NAME = ?
            LIMIT 1
            ',
            [
                $databaseName,
            ]
        )
    ) {
        $counter++;

        $databaseName =
            $base
            . '_'
            . $counter;
    }

    return $databaseName;
}

    /*
    |--------------------------------------------------------------------------
    | Generate Primary Domain
    |--------------------------------------------------------------------------
    */

    private function generatePrimaryDomain(
        string $slug
    ): string {

        $baseDomain =
            config(
                'saas.tenant_base_domain',
                'local'
            );

        $domain =
            $slug
            . '.'
            . $baseDomain;

        $counter = 1;

        while (
            Domain::on('central')
                ->where(
                    'domain',
                    $domain
                )
                ->exists()
        ) {

            $counter++;

            $domain =
                $slug
                . '-'
                . $counter
                . '.'
                . $baseDomain;
        }

        return $domain;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Admin Password
    |--------------------------------------------------------------------------
    */

    private function generateAdminPassword(): string
    {
        return Str::password(
            14,
            true,
            true,
            false,
            false
        );
    }

/*
|--------------------------------------------------------------------------
| Reset Tenant Admin Credentials
|--------------------------------------------------------------------------
|
| Generates a new temporary password and updates the tenant admin user.
| The plain password is returned only in this response.
|
*/

public function resetTenantAdminCredentials(
    Restaurant $restaurant
): array {

    /*
    |--------------------------------------------------------------------------
    | Tenant Database
    |--------------------------------------------------------------------------
    */

    $restaurant->loadMissing([
        'tenantDatabase',
    ]);

    $tenantDatabase =
        $restaurant->tenantDatabase;

    if (
        ! $tenantDatabase
    ) {
        throw new RuntimeException(
            'Tenant database was not found for this restaurant.'
        );
    }

    if (
        $tenantDatabase->status !== 'active'
    ) {
        throw new RuntimeException(
            'Tenant database is not active.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate New Password
    |--------------------------------------------------------------------------
    */

    $password =
        $this->generateAdminPassword();


    /*
    |--------------------------------------------------------------------------
    | Connect Tenant Database
    |--------------------------------------------------------------------------
    */

    try {

        $this->configureTenantConnection(
            $tenantDatabase
        );


        /*
        |--------------------------------------------------------------------------
        | Find Tenant Admin
        |--------------------------------------------------------------------------
        */

        $adminRoleId =
            DB::connection(
                self::TENANT_CONNECTION
            )
            ->table('roles')
            ->where(
                'name',
                'admin'
            )
            ->value('id');


        if (
            ! $adminRoleId
        ) {
            throw new RuntimeException(
                'Tenant admin role was not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Admin User
        |--------------------------------------------------------------------------
        */

        $admin =
            DB::connection(
                self::TENANT_CONNECTION
            )
            ->table('users')
            ->where(
                'role_id',
                $adminRoleId
            )
            ->orderBy(
                'id'
            )
            ->first();


        if (
            ! $admin
        ) {
            throw new RuntimeException(
                'Tenant admin user was not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        DB::connection(
            self::TENANT_CONNECTION
        )
        ->table('users')
        ->where(
            'id',
            $admin->id
        )
        ->update([
            'password' =>
                Hash::make(
                    $password
                ),

            'failed_login_attempts' =>
                0,

            'blocked_at' =>
                null,

            'updated_at' =>
                now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Cleanup
        |--------------------------------------------------------------------------
        */

        DB::disconnect(
            self::TENANT_CONNECTION
        );

        DB::purge(
            self::TENANT_CONNECTION
        );


        /*
        |--------------------------------------------------------------------------
        | Return Credentials
        |--------------------------------------------------------------------------
        */

        return [
            'username' =>
                $admin->username,

            'password' =>
                $password,
        ];

    } catch (
        Throwable $exception
    ) {

        try {

            DB::disconnect(
                self::TENANT_CONNECTION
            );

            DB::purge(
                self::TENANT_CONNECTION
            );

        } catch (Throwable) {
            // Ignore cleanup failure.
        }

        throw $exception;
    }
}

/*
|--------------------------------------------------------------------------
| Sync Existing Tenant Roles & Permissions
|--------------------------------------------------------------------------
|
| Used when tenant roles/permissions are updated after the tenant
| database has already been provisioned.
|
*/

public function syncTenantRolesAndPermissions(
    Restaurant $restaurant
): array {

    /*
    |--------------------------------------------------------------------------
    | Load Tenant Database
    |--------------------------------------------------------------------------
    */

    $restaurant->loadMissing([
        'tenantDatabase',
    ]);

    $tenantDatabase =
        $restaurant->tenantDatabase;

    if (! $tenantDatabase) {

        throw new RuntimeException(
            'Tenant database was not found for this restaurant.'
        );
    }

    if (
        $tenantDatabase->status !== 'active'
    ) {

        throw new RuntimeException(
            'Tenant database is not active.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Configure Tenant Connection
    |--------------------------------------------------------------------------
    */

    try {

        $this->configureTenantConnection(
            $tenantDatabase
        );


        /*
        |--------------------------------------------------------------------------
        | Run Permission Seeder
        |--------------------------------------------------------------------------
        |
        | Adds any newly created permissions.
        |
        */

        $permissionSeeder =
            app(
                \Database\Seeders\Tenant\TenantPermissionSeeder::class
            );

        $permissionSeeder->run();


        /*
        |--------------------------------------------------------------------------
        | Run Role Seeder
        |--------------------------------------------------------------------------
        |
        | Adds the manager role if it does not already exist.
        |
        */

        $roleSeeder =
            app(
                \Database\Seeders\Tenant\TenantRoleSeeder::class
            );

        $roleSeeder->run();


        /*
        |--------------------------------------------------------------------------
        | Sync Role Permissions
        |--------------------------------------------------------------------------
        |
        | Rebuilds missing role-permission mappings.
        |
        */

        $rolePermissionSeeder =
            app(
                \Database\Seeders\Tenant\TenantRolePermissionSeeder::class
            );

        $rolePermissionSeeder->run();


        /*
        |--------------------------------------------------------------------------
        | Read Final Data
        |--------------------------------------------------------------------------
        */

        $connection =
            DB::connection(
                self::TENANT_CONNECTION
            );


        $roles =
            $connection
                ->table('roles')
                ->orderBy('id')
                ->get([
                    'id',
                    'name',
                    'display_name',
                ]);


        $permissionsCount =
            $connection
                ->table('permissions')
                ->count();


        $rolePermissionCounts = [];


        foreach ($roles as $role) {

            $count =
                $connection
                    ->table('permission_role')
                    ->where(
                        'role_id',
                        $role->id
                    )
                    ->count();

            $rolePermissionCounts[
                $role->name
            ] = $count;
        }


        /*
        |--------------------------------------------------------------------------
        | Cleanup
        |--------------------------------------------------------------------------
        */

        DB::disconnect(
            self::TENANT_CONNECTION
        );

        DB::purge(
            self::TENANT_CONNECTION
        );


        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        return [

            'restaurant_id' =>
                $restaurant->id,

            'restaurant_name' =>
                $restaurant->name,

            'database' =>
                $tenantDatabase->database_name,

            'permissions_count' =>
                $permissionsCount,

            'roles' =>
                $roles,

            'role_permission_counts' =>
                $rolePermissionCounts,

        ];

    } catch (
        Throwable $exception
    ) {

        /*
        |--------------------------------------------------------------------------
        | Cleanup On Failure
        |--------------------------------------------------------------------------
        */

        try {

            DB::disconnect(
                self::TENANT_CONNECTION
            );

            DB::purge(
                self::TENANT_CONNECTION
            );

        } catch (Throwable) {
            // Ignore cleanup failure.
        }

        throw $exception;
    }
}
/*
|--------------------------------------------------------------------------
| Sync All Tenant Roles & Permissions
|--------------------------------------------------------------------------
|
| Syncs roles and permissions for every active restaurant that has
| an active tenant database.
|
*/

public function syncAllTenantRolesAndPermissions(): array
{
    $restaurants =
        Restaurant::on('central')
            ->where('status', 'active')
            ->whereHas(
                'tenantDatabase',
                function ($query) {
                    $query->where(
                        'status',
                        'active'
                    );
                }
            )
            ->orderBy('id')
            ->get();


    $results = [];

    $successCount = 0;
    $failedCount = 0;


    foreach ($restaurants as $restaurant) {

        try {

            $result =
                $this->syncTenantRolesAndPermissions(
                    $restaurant
                );


            $results[] = [
                'restaurant_id' =>
                    $restaurant->id,

                'restaurant_name' =>
                    $restaurant->name,

                'success' =>
                    true,

                'data' =>
                    $result,

                'error' =>
                    null,
            ];


            $successCount++;

        } catch (Throwable $exception) {

            report(
                $exception
            );


            $results[] = [
                'restaurant_id' =>
                    $restaurant->id,

                'restaurant_name' =>
                    $restaurant->name,

                'success' =>
                    false,

                'data' =>
                    null,

                'error' =>
                    $exception->getMessage(),
            ];


            $failedCount++;
        }
    }


    return [

        'total_restaurants' =>
            count($results),

        'successful' =>
            $successCount,

        'failed' =>
            $failedCount,

        'results' =>
            $results,
    ];
}
    /*
|--------------------------------------------------------------------------
| Drop Failed Tenant Database
|--------------------------------------------------------------------------
*/

private function dropDatabase(
    string $databaseName
): void {
    $safeName =
        str_replace(
            '`',
            '``',
            $databaseName
        );

    DB::connection(
        self::DATABASE_CONNECTION
    )->statement(
        "DROP DATABASE IF EXISTS `{$safeName}`"
    );
}
}
