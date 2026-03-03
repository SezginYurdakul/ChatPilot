<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    /**
     * List all sites owned by the authenticated user.
     *
     * GET /api/v1/admin/sites
     */
    public function index(Request $request): JsonResponse
    {
        $sites = $request->user()->sites;

        return response()->json([
            'sites' => $sites,
        ]);
    }

    /**
     * Register a new site and generate a unique API key.
     * The API key is used by the widget's X-Site-Key header.
     *
     * POST /api/v1/admin/sites
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'ai_provider' => 'nullable|in:gemini,openai,claude,none',
            'ai_api_key' => 'nullable|string',
            'ai_system_prompt' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $site = Site::create([
            'owner_id' => $request->user()->id,
            'name' => $request->input('name'),
            'domain' => $request->input('domain'),
            'api_key' => 'sk_' . Str::random(60),
            'ai_provider' => $request->input('ai_provider', 'none'),
            'ai_api_key' => $request->input('ai_api_key'),
            'ai_system_prompt' => $request->input('ai_system_prompt'),
            'settings' => $request->input('settings'),
        ]);

        return response()->json([
            'site' => $site,
            'api_key' => $site->api_key, // Show once on creation (hidden in model)
        ], 201);
    }

    /**
     * Update site settings, AI configuration, or other fields.
     *
     * PATCH /api/v1/admin/sites/{site}
     */
    public function update(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255',
            'ai_provider' => 'sometimes|in:gemini,openai,claude,none',
            'ai_api_key' => 'nullable|string',
            'ai_system_prompt' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $site->update($request->only([
            'name', 'domain', 'ai_provider', 'ai_api_key',
            'ai_system_prompt', 'settings', 'is_active',
        ]));

        return response()->json([
            'site' => $site,
        ]);
    }

    /**
     * Regenerate the site's API key.
     * Invalidates the old key immediately — widget must be updated.
     *
     * POST /api/v1/admin/sites/{site}/regenerate-key
     */
    public function regenerateKey(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $site->update([
            'api_key' => 'sk_' . Str::random(60),
        ]);

        return response()->json([
            'api_key' => $site->api_key,
            'message' => 'API key regenerated. Update your widget script.',
        ]);
    }

    /**
     * Verify that the authenticated user owns this site.
     * Aborts with 403 if the user doesn't own the site.
     */
    private function authorizeSiteOwnership(Request $request, Site $site): void
    {
        if ($site->owner_id !== $request->user()->id) {
            abort(403, 'You do not own this site.');
        }
    }
}
