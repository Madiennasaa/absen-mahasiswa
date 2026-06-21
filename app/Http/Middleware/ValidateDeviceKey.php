<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateDeviceKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceKey = $request->header('X-Device-Key');
        $validKey = config('app.device_key');
        
        if (!$deviceKey || $deviceKey !== $validKey) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Unauthorized device access'
            ], 401);
        }

        return $next($request);
    }
}
