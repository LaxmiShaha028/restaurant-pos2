<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponse;
use App\Traits\HasAuditLog;

class AuthController extends Controller
{
    use ApiResponse, HasAuditLog;

    private const MAX_FAILED_LOGIN_ATTEMPTS = 5;

    public function login(LoginRequest $request): JsonResponse
{
    $validated = $request->validated();

    $login = $validated['login'];

    $user = User::query()
        ->with([
            'role.permissions',
        ])
        ->where(function ($query) use ($login): void {
            $query
                ->where('email', $login)
                ->orWhere('username', $login);
        })
        ->first();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'The provided login credentials are incorrect.',
            'errors' => [
                'login' => [
                    'The provided login credentials are incorrect.',
                ],
            ],
        ], 401);
    }

    if (! $user->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Your account is inactive. Please contact the administrator.',
            'errors' => null,
        ], 403);
    }

    if ($user->blocked_at !== null) {
        return response()->json([
            'success' => false,
            'message' => 'Your account is blocked. Please contact the administrator.',
            'errors' => null,
        ], 403);
    }

    if (! $user->role || ! $user->role->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Your assigned role is unavailable.',
            'errors' => null,
        ], 403);
    }

    if (! Hash::check($validated['password'], $user->password)) {

        $this->recordFailedLogin($user);

        $remainingAttempts = max(
            0,
            self::MAX_FAILED_LOGIN_ATTEMPTS
            - $user->fresh()->failed_login_attempts
        );

        return response()->json([
            'success' => false,
            'message' => $remainingAttempts > 0
                ? 'The provided login credentials are incorrect.'
                : 'Your account has been blocked after too many failed login attempts.',
            'data' => [
                'remaining_attempts' => $remainingAttempts,
            ],
            'errors' => [
                'login' => [
                    'The provided login credentials are incorrect.',
                ],
            ],
        ], $remainingAttempts > 0 ? 401 : 403);
    }

    return DB::transaction(function () use ($user, $validated): JsonResponse {

        $user->forceFill([
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
        ])->save();

        /*
         * Delete old token with the same device name.
         * This prevents duplicate tokens for the same browser/device.
         */
        $deviceName = $validated['device_name'] ?? 'RMS Client';

        $user->tokens()
            ->where('name', $deviceName)
            ->delete();

        $plainTextToken = $user
            ->createToken($deviceName)
            ->plainTextToken;

        /*
         * Audit successful login.
         *
         * Never store password or access token
         * inside the audit log.
         */
        $this->audit(
            'authentication',
            'logged_in',
            $user,
            null,
            [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'logged_in_at' => $user->last_login_at,
            ],
            'User logged in: '.$user->username
        );

        $user->load([
            'role.permissions',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $plainTextToken,
                'user' => new UserResource($user),
            ],
            'errors' => null,
        ]);
    });
}

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->load([
            'role.permissions',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Authenticated user retrieved successfully.',
            'data' => [
                'user' => new UserResource($user),
            ],
            'errors' => null,
        ]);
    }

    public function logout(Request $request): JsonResponse
{
    $user = $request->user();

    DB::transaction(function () use ($request, $user): void {

        $user->forceFill([
            'last_logout_at' => now(),
        ])->save();

        /*
         * Only delete the token used for the current request.
         * Other devices remain logged in.
         */
        $request->user()
            ->currentAccessToken()
            ?->delete();
    });

    /*
     * Audit logout.
     */
    $this->audit(
        'authentication',
        'logged_out',
        $user,
        null,
        [
            'last_logout_at' => $user->last_logout_at,
        ],
        'User logged out: '.$user->username
    );

    return response()->json([
        'success' => true,
        'message' => 'Logout successful.',
        'data' => null,
        'errors' => null,
    ]);
}

    public function logoutAll(Request $request): JsonResponse
{
    $user = $request->user();

    DB::transaction(function () use ($user): void {

        $user->forceFill([
            'last_logout_at' => now(),
        ])->save();

        /*
         * Revoke all tokens from all devices.
         */
        $user->tokens()->delete();
    });

    /*
     * Audit logout from all devices.
     */
    $this->audit(
        'authentication',
        'logged_out_all',
        $user,
        null,
        [
            'last_logout_at' => $user->last_logout_at,
            'all_tokens_revoked' => true,
        ],
        'User logged out from all devices: '.$user->username
    );

    return response()->json([
        'success' => true,
        'message' => 'You have been logged out from all devices.',
        'data' => null,
        'errors' => null,
    ]);
}

    private function recordFailedLogin(User $user): void
{
    DB::transaction(function () use ($user): void {

        /*
         * Increment failed login attempts.
         */
        $user->increment('failed_login_attempts');

        $user->refresh();

        /*
         * Audit failed login attempt.
         */
        $this->audit(
            'authentication',
            'login_failed',
            $user,
            [
                'failed_login_attempts' =>
                    max(0, $user->failed_login_attempts - 1),
            ],
            [
                'failed_login_attempts' =>
                    $user->failed_login_attempts,
            ],
            'Failed login attempt for user: '.$user->username
        );

        /*
         * Block account after maximum failed attempts.
         */
        if (
            $user->failed_login_attempts
            >= self::MAX_FAILED_LOGIN_ATTEMPTS
        ) {

            $oldBlockedAt = $user->blocked_at;

            $user->forceFill([
                'blocked_at' => now(),
            ])->save();

            /*
             * Revoke all existing tokens when account is blocked.
             */
            $user->tokens()->delete();

            $user->refresh();

            /*
             * Audit account blocking.
             */
            $this->audit(
                'authentication',
                'account_blocked',
                $user,
                [
                    'blocked_at' => $oldBlockedAt,
                    'failed_login_attempts' =>
                        $user->failed_login_attempts,
                ],
                [
                    'blocked_at' => $user->blocked_at,
                    'failed_login_attempts' =>
                        $user->failed_login_attempts,
                ],
                'Account blocked after too many failed login attempts: '
                .$user->username
            );
        }
    });
}
}