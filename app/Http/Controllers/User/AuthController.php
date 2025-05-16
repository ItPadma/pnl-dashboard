<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            try {
                $token = JWTAuth::fromUser($user);
                $request->session()->regenerate();

                Log::info('Login berhasil dengan JWT', [
                    'user_id' => $user->id,
                    'auth_token' => $token
                ]);

                return redirect()->intended(route('dashboard'))
                    ->cookie('auth_token', $token, 120, '/', config('session.domain'), false, true);
            } catch (JWTException $e) {
                Log::error('Gagal membuat token JWT', ['error' => $e->getMessage()]);

                // Jika gagal membuat token, tetap login tanpa JWT
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        try {
            // Ambil token dari cookie
            $token = $request->cookie('auth_token');
            
            if ($token) {
                // Invalidasi token JWT jika valid
                if (is_string($token) && substr_count($token, '.') === 2) {
                    JWTAuth::setToken($token);
                    JWTAuth::invalidate();
                    Log::info('JWT Token berhasil diinvalidasi');
                }
            }
            
            // Logout dari session Laravel
            Auth::logout();
            
            // Hapus cookie auth_token
            $cookie = cookie()->forget('auth_token');
            
            Log::info('User berhasil logout');
            
            // Redirect ke halaman login dengan pesan sukses
            return redirect()->route('login')
                ->withCookie($cookie)
                ->with('success', 'Anda berhasil logout');
                
        } catch (\Exception $e) {
            Log::error('Error saat logout', ['error' => $e->getMessage()]);
            
            // Tetap hapus cookie meskipun terjadi error
            $cookie = cookie()->forget('auth_token');
            
            return redirect()->route('login')
                ->withCookie($cookie)
                ->with('error', 'Terjadi kesalahan saat logout');
        }
    }
}
