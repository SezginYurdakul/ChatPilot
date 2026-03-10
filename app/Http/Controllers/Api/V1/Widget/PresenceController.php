<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Events\VisitorStatusChanged;
use App\Http\Controllers\Controller;
use App\Support\VisitorPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $conversation = $request->attributes->get('conversation');

        VisitorPresence::heartbeat($conversation->id);
        VisitorStatusChanged::dispatch($conversation->id, true);

        return response()->json([
            'online' => true,
        ]);
    }

    public function offline(Request $request): JsonResponse
    {
        $conversation = $request->attributes->get('conversation');

        VisitorPresence::markOffline($conversation->id);
        VisitorStatusChanged::dispatch($conversation->id, false);

        return response()->json([
            'online' => false,
        ]);
    }
}
