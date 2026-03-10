<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SettingsValidator;
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
        $sites = $request->user()
            ->accessibleSitesQuery()
            ->orderBy('created_at')
            ->get();

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
        $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'ai_provider' => 'nullable|in:gemini,openai,claude,none',
            'ai_api_key' => 'nullable|string',
            'ai_system_prompt' => 'nullable|string',
        ], SettingsValidator::rules()));

        $site = Site::create([
            'owner_id' => $request->user()->id,
            'name' => $request->input('name'),
            'domain' => $request->input('domain'),
            'api_key' => 'sk_' . Str::random(60),
            'ai_provider' => $request->input('ai_provider', 'none'),
            'ai_api_key' => $request->input('ai_api_key'),
            'ai_system_prompt' => $request->input('ai_system_prompt'),
            'settings' => $request->has('settings')
                ? SettingsValidator::filterUnknownKeys($request->input('settings'))
                : null,
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
        $this->authorizeSiteAccess($request, $site);

        $request->validate(array_merge([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255',
            'ai_provider' => 'sometimes|in:gemini,openai,claude,none',
            'ai_api_key' => 'nullable|string',
            'ai_system_prompt' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ], SettingsValidator::rules()));

        $fields = $request->only([
            'name', 'domain', 'ai_provider', 'ai_api_key',
            'ai_system_prompt', 'is_active',
        ]);

        // Deep merge filtered settings so partial updates don't erase existing values
        // and unknown keys are rejected before reaching the database.
        if ($request->has('settings')) {
            $filtered = SettingsValidator::filterUnknownKeys($request->input('settings'));
            $fields['settings'] = array_replace_recursive(
                $site->settings ?? [],
                $filtered,
            );
        }

        $site->update($fields);

        return response()->json([
            'site' => $site,
        ]);
    }

    /**
     * Return the settings schema so the frontend can dynamically build forms.
     * Each group contains fields with type, default, min/max, and options.
     *
     * GET /api/v1/admin/sites/settings-schema
     */
    public function settingsSchema(): JsonResponse
    {
        return response()->json([
            'version' => config('chatpilot.settings_schema_version'),
            'schema' => config('chatpilot.settings_schema'),
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
        $this->authorizeSiteAccess($request, $site);

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
    private function authorizeSiteAccess(Request $request, Site $site): void
    {
        $user = $request->user();

        if ($user->isSuperAdmin() || $site->owner_id === $user->id) {
            return;
        }

        if (! $user->assignedSites()->where('sites.id', $site->id)->exists()) {
            abort(403, 'You do not have access to this site.');
        }
    }
}
