<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSiteKey
{
    /**
     * Validate the X-Site-Key header from widget requests.
     * Looks up the site by API key, rejects if missing/invalid/inactive.
     * Attaches the Site model to the request for downstream use.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Site-Key');

        if (! $apiKey) {
            return response()->json([
                'error' => 'missing_site_key',
                'message' => 'X-Site-Key header is required.',
            ], 401);
        }

        $site = Site::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (! $site) {
            return response()->json([
                'error' => 'invalid_site_key',
                'message' => 'Invalid or inactive site key.',
            ], 401);
        }

        // Attach site to request so controllers can access it
        $request->merge(['site' => $site]);
        $request->attributes->set('site', $site);

        return $next($request);
    }
}
