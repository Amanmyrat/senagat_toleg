<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $allowedIps = array_filter(explode(',', env('ALLOWED_IPS', '')));

        Log::info('IP Check Middleware worked', [
            'client_ip' => $clientIp,
            'allowed_ips' => $allowedIps,
        ]);

        if (! in_array($clientIp, $allowedIps)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 403,
                    'message' => 'Your IP is not allowed'
                ],
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}
