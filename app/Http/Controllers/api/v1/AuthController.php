<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Authenticate user and return JWT tokens via cookies.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Generate JWT access token
        $accessToken = JWTAuth::fromUser($user);

        // Generate refresh token
        $refreshToken = Str::random(64);

        // Store refresh token hash in database
        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(14),
        ]);

        // Return tokens as HttpOnly cookies
        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
        ])->cookie('access_token', $accessToken, 1, '/', null, false, true, false, 'lax')
          ->cookie('refresh_token', $refreshToken, 60 * 24 * 14, '/', null, false, true, false, 'lax');
    }

    /**
     * Return the authenticated user's profile.
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'data' => Auth::user(),
        ]);
    }

    /**
     * Refresh the access token using the refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $existingRefreshToken = $request->cookie('refresh_token');

        if (!$existingRefreshToken) {
            return response()->json([
                'message' => 'Refresh token not provided.'
            ], 401);
        }

        // Find the refresh token in database
        $refreshTokenRecord = RefreshToken::where('token_hash', hash('sha256', $existingRefreshToken))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->with('user')
            ->first();

        if (!$refreshTokenRecord) {
            return response()->json([
                'message' => 'Invalid or expired refresh token.'
            ], 401);
        }

        // Revoke the old refresh token (token rotation)
        $refreshTokenRecord->update(['revoked_at' => now()]);

        // Generate new tokens
        $user = $refreshTokenRecord->user;
        $newAccessToken = JWTAuth::fromUser($user);
        $newRefreshToken = Str::random(64);

        // Store new refresh token
        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $newRefreshToken),
            'expires_at' => now()->addDays(14),
        ]);

        // Return new tokens as cookies
        return response()->json([
            'message' => 'Token refreshed successfully.',
        ])->cookie('access_token', $newAccessToken, 1, '/', null, false, true, false, 'lax')
          ->cookie('refresh_token', $newRefreshToken, 60 * 24 * 14, '/', null, false, true, false, 'lax');
    }

    /**
     * Logout user and revoke all refresh tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            // Revoke all active refresh tokens for this user
            RefreshToken::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->update(['revoked_at' => now()]);

            // Invalidate JWT token
            JWTAuth::invalidate(JWTAuth::getToken());
        }

        // Clear cookies
        return response()->json([
            'message' => 'Successfully logged out.'
        ])->cookie('access_token', '', -1)
          ->cookie('refresh_token', '', -1);
    }
}
