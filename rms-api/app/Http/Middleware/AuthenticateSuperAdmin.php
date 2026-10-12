<?php

namespace App\Http\Middleware;

use App\Models\Central\SuperAdmin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSuperAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
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
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Token Format
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
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
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
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Hash Token
        |--------------------------------------------------------------------------
        */

        $hashedToken =
            hash(
                'sha256',
                $plainToken
            );

        /*
        |--------------------------------------------------------------------------
        | DIRECT CENTRAL DATABASE LOOKUP
        |--------------------------------------------------------------------------
        */

        $tokenRow =
            DB::connection('central')
                ->table('personal_access_tokens')
                ->where(
                    'id',
                    $tokenId
                )
                ->where(
                    'token',
                    $hashedToken
                )
                ->where(
                    'name',
                    'central-api-token'
                )
                ->first();

        if (
            ! $tokenRow
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
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
            SuperAdmin::class
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
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
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Super Admin From CENTRAL DB
        |--------------------------------------------------------------------------
        */

        $superAdmin =
            SuperAdmin::on('central')
                ->where(
                    'id',
                    $tokenRow->tokenable_id
                )
                ->first();

        if (
            ! $superAdmin
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Active Check
        |--------------------------------------------------------------------------
        */

        if (
            ! $superAdmin->is_active
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Super administrator account is inactive.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Blocked Check
        |--------------------------------------------------------------------------
        */

        if (
            $superAdmin->blocked_at !== null
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Super administrator account is blocked.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Hydrate Central Access Token
        |--------------------------------------------------------------------------
        */

        $accessToken =
            new \App\Models\Central\CentralPersonalAccessToken();

        $accessToken->setConnection(
            'central'
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
        | Attach Current Token
        |--------------------------------------------------------------------------
        */

        $superAdmin->withAccessToken(
            $accessToken
        );

        /*
        |--------------------------------------------------------------------------
        | Set Request User
        |--------------------------------------------------------------------------
        */

        $request->setUserResolver(
            static fn (): SuperAdmin => $superAdmin
        );

        /*
        |--------------------------------------------------------------------------
        | Update Last Used
        |--------------------------------------------------------------------------
        */

        DB::connection('central')
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
        | Continue
        |--------------------------------------------------------------------------
        */

        return $next(
            $request
        );
    }
}