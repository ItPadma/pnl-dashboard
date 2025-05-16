<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionDebugMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Request masuk', [
            'session_id' => session()->getId(),
            'cookies' => $request->cookies->all(),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip()
        ]);
        
        $response = $next($request);
        
        Log::info('Response keluar', [
            'session_id' => session()->getId(),
            'cookies_response' => $response->headers->getCookies(),
            'status' => $response->getStatusCode()
        ]);
        
        return $response;
    }
}
