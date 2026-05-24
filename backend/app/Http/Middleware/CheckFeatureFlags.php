<?php

namespace App\Http\Middleware;

use App\Services\AppSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureFlags
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('DISABLE_MAINTENANCE_MODE', false)) {
            return $next($request);
        }

        $settings = app(AppSettingsService::class);

        // If maintenance mode is not active, proceed immediately.
        if (! $settings->isMaintenanceMode()) {
            return $next($request);
        }

        // Try to resolve the current user from several sources. When this
        // middleware runs before the auth middleware, $request->user() may be
        // null even when the request includes a valid bearer token. We try
        // multiple approaches so admins authenticated via cookie or token are
        // recognized and allowed to bypass maintenance.
        $user = $request->user() ?? auth()->user();

        $token = null;
        if (! $user) {
            $token = $request->bearerToken();
            if ($token && class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                $user = $pat?->tokenable;
            }
        }

        // Debug info to help trace why an admin might still be blocked.
        try {
            Log::debug('CheckFeatureFlags: maintenance active, resolved user', [
                'has_token' => $token ? true : false,
                'token' => $token ? substr($token, 0, 8) . '...' : null,
                'user_id' => $user?->id ?? null,
                'user_email' => $user?->email ?? null,
                'user_is_admin' => $user?->is_admin ?? null,
            ]);
        } catch (\Throwable $e) {
            // swallow logging errors to avoid interfering with request flow
        }

        if ($user && ($user->is_admin ?? false)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'The platform is currently under maintenance. Please try again later.',
        ], 503);
    }
}
