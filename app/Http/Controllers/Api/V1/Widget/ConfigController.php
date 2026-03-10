<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Http\Controllers\Controller;
use App\Support\AdminPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Return the widget configuration for the site.
     * Used by the widget on initial load to get colors, greeting, language, etc.
     *
     * GET /api/v1/site/config
     * Header: X-Site-Key
     */
    public function show(Request $request): JsonResponse
    {
        $site = $request->attributes->get('site');

        return response()->json([
            'site_id' => (string) $site->id,
            'settings' => $site->settings,
            'admin_online' => AdminPresence::isOnline((string) $site->id),
        ]);
    }
}
