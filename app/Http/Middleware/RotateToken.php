<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class RotateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only rotate token for authenticated API requests
        if ($request->is('api/*') && Auth::guard('api')->check()) {
            try {
                /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
                $guard = Auth::guard('api');

                // Generate a new token with the same payload (same expiry)
                $user = $guard->user();
                if ($user) {
                    $newToken = $guard->login($user);

                    // Log token rotation for security auditing
                    Log::info('JWT token rotated', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    // Add the new token to the response headers
                    $response->headers->set('Authorization', 'Bearer '.$newToken);
                    $response->headers->set('X-Token-Rotated', 'true');
                }
            } catch (JWTException $e) {
                // Log token rotation failure for debugging
                Log::warning('JWT token rotation failed', [
                    'error' => $e->getMessage(),
                    'user_id' => Auth::guard('api')->id(),
                    'ip_address' => $request->ip(),
                ]);

                // If token rotation fails, continue with the original response
                // Don't fail the request due to token rotation issues
            }
        }

        return $response;
    }
}
