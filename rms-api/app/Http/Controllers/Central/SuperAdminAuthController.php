<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\CentralPersonalAccessToken;
use App\Models\Central\SuperAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Tenant\TenantProvisioningService;

class SuperAdminAuthController extends Controller
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

        $login =
            strtolower(
                trim(
                    $validated['login']
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Find Super Admin
        |--------------------------------------------------------------------------
        |
        | Login can use:
        |
        | username
        | email
        |
        */

        $superAdmin =
            SuperAdmin::on('central')
                ->where(
                    function ($query) use ($login): void {

                        $query
                            ->where(
                                'username',
                                $login
                            )
                            ->orWhere(
                                'email',
                                $login
                            );
                    }
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Credentials
        |--------------------------------------------------------------------------
        */

        if (
            ! $superAdmin
            ||
            ! Hash::check(
                $validated['password'],
                $superAdmin->password
            )
        ) {

            if ($superAdmin) {

                $superAdmin->increment(
                    'failed_login_attempts'
                );
            }

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Invalid username/email or password.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Active
        |--------------------------------------------------------------------------
        */

        if (
            ! $superAdmin->is_active
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Super administrator account is inactive.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Blocked
        |--------------------------------------------------------------------------
        */

        if (
            $superAdmin->blocked_at !== null
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Super administrator account is blocked.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Revoke Existing Central Tokens
        |--------------------------------------------------------------------------
        */

        CentralPersonalAccessToken::on('central')
            ->where(
                'tokenable_type',
                SuperAdmin::class
            )
            ->where(
                'tokenable_id',
                $superAdmin->id
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Reset Security Fields
        |--------------------------------------------------------------------------
        */

        $superAdmin->forceFill([
            'failed_login_attempts' =>
                0,

            'last_login_at' =>
                now(),
        ])->saveQuietly();

        /*
        |--------------------------------------------------------------------------
        | Generate Plain Token
        |--------------------------------------------------------------------------
        */

        $plainToken =
            Str::random(40);

        /*
        |--------------------------------------------------------------------------
        | Store Hashed Token
        |--------------------------------------------------------------------------
        */

        $accessToken =
    new CentralPersonalAccessToken();

$accessToken->setConnection(
    'central'
);

$accessToken->forceFill([

    'tokenable_type' =>
        SuperAdmin::class,

    'tokenable_id' =>
        $superAdmin->id,

    'name' =>
        'central-api-token',

    'token' =>
        hash(
            'sha256',
            $plainToken
        ),

    'abilities' =>
        json_encode([
            '*',
        ]),

    'expires_at' =>
        null,

])->save();

        /*
        |--------------------------------------------------------------------------
        | Final Bearer Token
        |--------------------------------------------------------------------------
        */

        $token =
            $accessToken->id
            . '|'
            . $plainToken;

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Super administrator login successful.',

            'data' => [
                'token' =>
                    $token,

                'token_type' =>
                    'Bearer',

                'super_admin' => [
                    'id' =>
                        $superAdmin->id,

                    'name' =>
                        $superAdmin->name,

                    'username' =>
                        $superAdmin->username,

                    'email' =>
                        $superAdmin->email,
                ],
            ],

            'errors' =>
                null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Current Super Admin
    |--------------------------------------------------------------------------
    */

    public function me(
        Request $request
    ): JsonResponse {

        /** @var SuperAdmin $superAdmin */
        $superAdmin =
            $request->user();

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Super administrator retrieved successfully.',

            'data' => [
                'id' =>
                    $superAdmin->id,

                'name' =>
                    $superAdmin->name,

                'username' =>
                    $superAdmin->username,

                'email' =>
                    $superAdmin->email,

                'is_active' =>
                    $superAdmin->is_active,
            ],

            'errors' =>
                null,
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

        /** @var SuperAdmin $superAdmin */
        $superAdmin =
            $request->user();

        $currentToken =
            $superAdmin->currentAccessToken();

        if (
            $currentToken
        ) {
            $currentToken->delete();
        }

        $superAdmin->forceFill([
            'last_logout_at' =>
                now(),
        ])->saveQuietly();

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Super administrator logout successful.',

            'data' =>
                null,

            'errors' =>
                null,
        ]);
    }
}