<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(
        Request $request
    ): JsonResponse {

$validated =
    $request->validate([
        'tenant_key' => [
            'required',
            'string',
            'max:150',
        ],

        'login' => [
            'required',
            'string',
            'max:150',
        ],

        'password' => [
            'required',
            'string',
            'max:255',
        ],
    ]);


/*
|--------------------------------------------------------------------------
| Normalize Login Data
|--------------------------------------------------------------------------
*/

$tenantKey =
    trim(
        $validated['tenant_key']
    );

$login =
    trim(
        $validated['login']
    );

$password =
    $validated['password'];


        /*
        |--------------------------------------------------------------------------
        | File Cache For Tenant Login Rate Limit
        |--------------------------------------------------------------------------
        */

        $rateLimitKey =
            'tenant-login:'
            . Str::lower(
                $login
            )
            . ':'
            . $request->ip();

        $cache =
            Cache::store('file');

        $attemptKey =
            $rateLimitKey
            . ':attempts';

        $lockoutKey =
            $rateLimitKey
            . ':locked';

        /*
        |--------------------------------------------------------------------------
        | Check Lockout
        |--------------------------------------------------------------------------
        */

        if (
            $cache->has(
                $lockoutKey
            )
        ) {

            return response()->json([
                'message' =>
                    'Too many login attempts. Please try again later.',
            ], 429);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Tenant User
        |--------------------------------------------------------------------------
        |
        | ResolveTenant has already switched the default connection
        | to the current tenant database.
        |
        | Load role + permissions because the Vue frontend uses
        | permissions for route authorization.
        |
        */

$user =
    User::on('tenant')
        ->with([
            'role.permissions',
        ])
        ->where(function ($query) use ($login) {

            $query
                ->where(
                    'username',
                    $login
                )
                ->orWhere(
                    'email',
                    $login
                );

        })
        ->first();

        /*
        |--------------------------------------------------------------------------
        | Invalid Credentials
        |--------------------------------------------------------------------------
        */

        if (
            ! $user
            ||
            ! Hash::check(
                $password,
                $user->password
            )
        ) {

            $attempts =
                (int) (
                    $cache->get(
                        $attemptKey,
                        0
                    )
                );

            $attempts++;

            if (
                $attempts >= 5
            ) {

                $cache->put(
                    $lockoutKey,
                    true,
                    now()->addSeconds(60)
                );

                $cache->forget(
                    $attemptKey
                );

            } else {

                $cache->put(
                    $attemptKey,
                    $attempts,
                    now()->addSeconds(60)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Optional User Security Counter
            |--------------------------------------------------------------------------
            */

            if (
                $user
            ) {

                $user->increment(
                    'failed_login_attempts'
                );
            }

            return response()->json([
                'message' =>
                    'Invalid username/email or password.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Active User Check
        |--------------------------------------------------------------------------
        */

        if (
            ! $user->is_active
        ) {

            return response()->json([
                'message' =>
                    'Your account is inactive.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Blocked User Check
        |--------------------------------------------------------------------------
        */

        if (
            $user->blocked_at !== null
        ) {

            return response()->json([
                'message' =>
                    'Your account is currently blocked.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Login Rate Limit
        |--------------------------------------------------------------------------
        */

        $cache->forget(
            $attemptKey
        );

        $cache->forget(
            $lockoutKey
        );

        /*
        |--------------------------------------------------------------------------
        | Reset Login Security Fields
        |--------------------------------------------------------------------------
        */

        $user->forceFill([
            'failed_login_attempts' =>
                0,

            'last_login_at' =>
                now(),
        ])->saveQuietly();

        /*
        |--------------------------------------------------------------------------
        | Revoke Existing Tokens
        |--------------------------------------------------------------------------
        */

        $user
            ->tokens()
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Create Sanctum Token
        |--------------------------------------------------------------------------
        */

        $token =
            $user
                ->createToken(
                    'tenant-api-token'
                )
                ->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $user->role
                ? $user
                    ->role
                    ->permissions
                    ->pluck('name')
                    ->values()
                    ->toArray()
                : [];

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'Login successful.',

            'token' =>
                $token,

            'token_type' =>
                'Bearer',

            'user' => [
                'id' =>
                    $user->id,

                'name' =>
                    $user->name,

                'username' =>
                    $user->username,

                'email' =>
                    $user->email,

                'is_active' =>
                    $user->is_active,

                'role' =>
                    $user->role
                        ? [
                            'id' =>
                                $user->role->id,

                            'name' =>
                                $user->role->name,

                            'display_name' =>
                                $user
                                    ->role
                                    ->display_name,
                        ]
                        : null,

                'permissions' =>
                    $permissions,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(
        Request $request
    ): JsonResponse {

        $user =
            $request->user();

        if (
            $user
        ) {

            $currentToken =
                $user->currentAccessToken();

            if (
                $currentToken
            ) {

                $currentToken->delete();
            }

            $user->forceFill([
                'last_logout_at' =>
                    now(),
            ])->saveQuietly();
        }

        return response()->json([
            'message' =>
                'Logout successful.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    public function me(
        Request $request
    ): JsonResponse {

        $user =
            $request->user();

        /*
        |--------------------------------------------------------------------------
        | Load Role + Permissions
        |--------------------------------------------------------------------------
        */

        $user->loadMissing([
            'role.permissions',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $user->role
                ? $user
                    ->role
                    ->permissions
                    ->pluck('name')
                    ->values()
                    ->toArray()
                : [];

        return response()->json([
            'user' => [
                'id' =>
                    $user->id,

                'name' =>
                    $user->name,

                'username' =>
                    $user->username,

                'email' =>
                    $user->email,

                'is_active' =>
                    $user->is_active,

                'role' =>
                    $user->role
                        ? [
                            'id' =>
                                $user->role->id,

                            'name' =>
                                $user->role->name,

                            'display_name' =>
                                $user
                                    ->role
                                    ->display_name,
                        ]
                        : null,

                'permissions' =>
                    $permissions,
            ],
        ]);
    }
}