<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthnCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Izinkan akses ke rute autentikasi tanpa perlu login
        if ($request->is('login') || $request->is('register') || $request->is('password/*') || $request->is('logout')) {
            return $next($request);
        }

        // Coba autentikasi dengan JWT token jika ada
        $encryptedToken = $request->cookie('auth_token');

        if ($encryptedToken) {
            try {
                // Decrypt the token if it's encrypted
                // If you're using Laravel's cookie encryption, the token is already decrypted
                // by the time it reaches the middleware, but the format might be incorrect

                // Check if the token has the correct JWT format (3 segments separated by dots)
                if (is_string($encryptedToken) && substr_count($encryptedToken, '.') === 2) {
                    JWTAuth::setToken($encryptedToken);
                    $user = JWTAuth::authenticate();

                    if ($user) {
                        Auth::login($user);
                        Log::info('JWT Auth berhasil', ['user_id' => $user->id]);
                    }
                } else {
                    // The token might be encrypted or in an incorrect format
                    Log::error('JWT Token format invalid', ['token_format' => substr($encryptedToken, 0, 10) . '...']);
                }
            } catch (TokenExpiredException $e) {
                Log::error('JWT Token expired', ['error' => $e->getMessage()]);
            } catch (TokenInvalidException $e) {
                Log::error('JWT Token invalid', ['error' => $e->getMessage()]);
            } catch (JWTException $e) {
                Log::error('JWT Auth error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        // Periksa apakah user sudah terotentikasi
        if (!Auth::check()) {
            Log::info('User tidak terotentikasi, redirect ke login');

            // Simpan URL yang dimaksud untuk redirect setelah login (jika ini adalah request GET)
            if ($request->isMethod('get')) {
                session()->put('url.intended', url()->current());
            }

            return redirect()->route('login');
        }

        // User sudah terotentikasi, lanjutkan dengan request
        Log::info('User terotentikasi, melanjutkan request', ['user_id' => Auth::id()]);
        return $next($request);
    }
}
