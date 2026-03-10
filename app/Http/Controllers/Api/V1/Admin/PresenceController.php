<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\AdminStatusChanged;
use App\Http\Controllers\Controller;
use App\Support\AdminPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return response()->json([
                'online' => false,
            ]);
        }

        $siteIds = $request->user()->accessibleSiteIds();

        foreach ($siteIds as $siteId) {
            AdminPresence::heartbeat($siteId);
            AdminStatusChanged::dispatch((string) $siteId, true);
        }

        return response()->json([
            'online' => true,
        ]);
    }

    public function offline(Request $request): JsonResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return response()->json([
                'online' => false,
            ]);
        }

        $siteIds = $request->user()->accessibleSiteIds();

        foreach ($siteIds as $siteId) {
            AdminPresence::markOffline($siteId);
            AdminStatusChanged::dispatch((string) $siteId, false);
        }

        return response()->json([
            'online' => false,
        ]);
    }
}
