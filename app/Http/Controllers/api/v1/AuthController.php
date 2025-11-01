<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    /**
     * Authenticate user and return a JWT.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');
            if (! $token = $guard->attempt($credentials)) {
                // Log failed login attempt
                Log::warning('Failed login attempt', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'message' => 'The provided credentials are incorrect.'
                ], 401);
            }

            // Log successful login
            $user = $guard->user();
            Log::info('User logged in', [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'ip' => $request->ip(),
            ]);

            return $this->respondWithToken($token);

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $credentials['email'],
            ]);

            return response()->json([
                'message' => 'An error occurred during authentication.'
            ], 500);
        }
    }

    /**
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $user,
            'message' => 'User profile retrieved successfully.'
        ]);
    }

    /**
     * Invalidate the current token.
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Not authenticated.'
                ], 401);
            }

            // Log logout event
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            Auth::guard('api')->logout();

            return response()->json([
                'message' => 'Successfully logged out.'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred during logout.'
            ], 500);
        }
    }

    /**
     * Refresh the token and return a new one.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');
            $token = $guard->refresh();

            // Log token refresh
            $user = $guard->user();
            if ($user) {
                Log::info('Token refreshed', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);
            }

            return $this->respondWithToken($token);

        } catch (TokenExpiredException $e) {
            Log::warning('Token refresh failed - expired', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Token has expired and cannot be refreshed. Please login again.'
            ], 401);

        } catch (TokenInvalidException $e) {
            Log::warning('Token refresh failed - invalid', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Token is invalid. Please login again.'
            ], 401);

        } catch (JWTException $e) {
            Log::error('Token refresh failed - JWT error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Could not refresh token. Please login again.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Token refresh failed - general error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'An error occurred while refreshing token.'
            ], 500);
        }
    }

    /**
     * Helper to format token response consistently.
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');
        $ttlSeconds = $guard->factory()->getTTL() * 60;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlSeconds,
        ]);
    }
}
