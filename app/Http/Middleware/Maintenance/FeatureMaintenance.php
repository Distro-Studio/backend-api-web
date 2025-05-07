<?php

namespace App\Http\Middleware\Maintenance;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Cek apakah fitur sedang dimatikan (config)
        $disabledFeatures = config('feature.disabled', []); // dari config/feature.php

        if (in_array($feature, $disabledFeatures)) {
            return response()->json([
                'status' => 503,
                'message' => "Fitur '$feature' sedang dalam perawatan atau perbaikan.",
            ], 503);
        }

        return $next($request);
    }
}
