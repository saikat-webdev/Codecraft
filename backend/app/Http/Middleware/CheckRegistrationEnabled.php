<?php

namespace App\Http\Middleware;

use App\Services\AppSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(AppSettingsService::class);

        if ($settings->isMaintenanceMode()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is disabled while the platform is in maintenance mode.',
            ], 503);
        }

        if (! $settings->isRegistrationEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is currently disabled.',
            ], 403);
        }

        return $next($request);
    }
}
