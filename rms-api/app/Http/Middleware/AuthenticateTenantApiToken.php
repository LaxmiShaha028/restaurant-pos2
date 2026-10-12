<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Central\Subscription;
use Illuminate\Support\Facades\Auth;

class AuthenticateTenantApiToken
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Tenant Connection Safety
        |--------------------------------------------------------------------------
        |
        | ResolveTenant must run before this middleware.
        |
        */
//         return response()->json([
//     'host' =>
//         $request->getHost(),

//     'default_connection' =>
//         DB::getDefaultConnection(),

//     'database' =>
//         DB::connection('tenant')
//             ->getDatabaseName(),
// ]);

        if (
            DB::getDefaultConnection()
            !==
            'tenant'
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Tenant authentication context is not active.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | Bearer Token
        |--------------------------------------------------------------------------
        */

        $bearerToken =
            $request->bearerToken();

        if (
            ! $bearerToken
            ||
            trim($bearerToken) === ''
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Sanctum Token Format
        |--------------------------------------------------------------------------
        |
        | Expected:
        |
        | 123|plain-text-token
        |
        */

        $parts =
            explode(
                '|',
                $bearerToken,
                2
            );

        if (
            count($parts) !== 2
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        $tokenId =
            (int) $parts[0];

        $plainToken =
            $parts[1];

        if (
            $tokenId <= 0
            ||
            trim($plainToken) === ''
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Hash Plain Token
        |--------------------------------------------------------------------------
        */

        $hashedToken =
            hash(
                'sha256',
                $plainToken
            );

        /*
        |--------------------------------------------------------------------------
        | Direct Tenant Token Lookup
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do NOT use:
        |
        |     Sanctum::auth
        |     PersonalAccessToken::findToken()
        |
        | here.
        |
        | We explicitly query the current tenant database.
        |
        */

        $tokenRow =
            DB::connection('tenant')
                ->table('personal_access_tokens')
                ->where(
                    'id',
                    $tokenId
                )
                ->where(
                    'token',
                    $hashedToken
                )
                ->first();

        if (
            ! $tokenRow
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Token Expiration
        |--------------------------------------------------------------------------
        */

        if (
            $tokenRow->expires_at !== null
            &&
            now()->greaterThan(
                $tokenRow->expires_at
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Tokenable Type Protection
        |--------------------------------------------------------------------------
        */

        if (
            $tokenRow->tokenable_type
            !==
            User::class
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Tenant User Explicitly
        |--------------------------------------------------------------------------
        */

        $user =
            User::on('tenant')
                ->where(
                    'id',
                    $tokenRow->tokenable_id
                )
                ->first();

        if (
            ! $user
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Tenant User Connection Protection
        |--------------------------------------------------------------------------
        */

        if (
            $user->getConnectionName()
            !==
            'tenant'
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Invalid tenant user connection.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Active User
        |--------------------------------------------------------------------------
        */

        if (
            ! $user->is_active
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your account is inactive.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Blocked User
        |--------------------------------------------------------------------------
        */

        if (
            $user->blocked_at !== null
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your account is currently blocked.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 403);
        }

        /*
|--------------------------------------------------------------------------
| Tenant Subscription Protection
|--------------------------------------------------------------------------
*/

$restaurant =
    $request->attributes->get(
        'tenant'
    );

if (! $restaurant) {
    return response()->json([
        'success' =>
            false,

        'message' =>
            'Tenant context could not be resolved.',

        'data' =>
            null,

        'errors' =>
            null,
    ], 500);
}

$subscription =
    Subscription::on('central')
        ->where(
            'restaurant_id',
            $restaurant->id
        )
        ->latest('id')
        ->first();

if (! $subscription) {
    return response()->json([
        'success' =>
            false,

        'message' =>
            'Restaurant subscription could not be found.',

        'data' =>
            null,

        'errors' =>
            null,
    ], 403);
}

if (
    $subscription->status !== 'active'
) {
    return response()->json([
        'success' =>
            false,

        'message' =>
            'Restaurant subscription is not active.',

        'data' =>
            [
                'subscription_status' =>
                    $subscription->status,
            ],

        'errors' =>
            null,
    ], 403);
}

if (
    $subscription->end_at !== null
    &&
    now()->greaterThanOrEqualTo(
        $subscription->end_at
    )
) {
    return response()->json([
        'success' =>
            false,

        'message' =>
            'Restaurant subscription has expired.',

        'data' =>
            [
                'subscription_status' =>
                    'expired',

                'subscription_end_at' =>
                    $subscription->end_at,
            ],

        'errors' =>
            null,
    ], 403);
}

        /*
        |--------------------------------------------------------------------------
        | Hydrate Token Model
        |--------------------------------------------------------------------------
        |
        | Keep currentAccessToken() compatibility for existing controllers.
        |
        */

        $accessToken =
            new PersonalAccessToken();

        $accessToken->setConnection(
            'tenant'
        );

        $accessToken->forceFill([
            'id' =>
                $tokenRow->id,

            'tokenable_type' =>
                $tokenRow->tokenable_type,

            'tokenable_id' =>
                $tokenRow->tokenable_id,

            'name' =>
                $tokenRow->name,

            'token' =>
                $tokenRow->token,

            'abilities' =>
                $tokenRow->abilities,

            'last_used_at' =>
                $tokenRow->last_used_at,

            'expires_at' =>
                $tokenRow->expires_at,

            'created_at' =>
                $tokenRow->created_at,

            'updated_at' =>
                $tokenRow->updated_at,
        ]);

        $accessToken->exists = true;

        /*
|--------------------------------------------------------------------------
| Attach Current Access Token
|--------------------------------------------------------------------------
|
| Keep Sanctum compatibility.
|
*/

$user->withAccessToken(
    $accessToken
);


/*
|--------------------------------------------------------------------------
| Register Authenticated Tenant User
|--------------------------------------------------------------------------
*/

Auth::guard()
    ->setUser(
        $user
    );


$request->setUserResolver(
    static function () use ($user) {

        return $user;

    }
);


/*
|--------------------------------------------------------------------------
| Update Last Used At
|--------------------------------------------------------------------------
*/

DB::connection('tenant')
    ->table('personal_access_tokens')
    ->where(
        'id',
        $tokenId
    )
    ->update([
        'last_used_at' =>
            now(),

        'updated_at' =>
            now(),
    ]);


/*
|--------------------------------------------------------------------------
| Continue Request
|--------------------------------------------------------------------------
*/

return $next(
    $request
);
    }
}
