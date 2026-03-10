<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with([
                'ownedSites:id,name,owner_id',
                'assignedSites:id,name',
            ])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'users' => $users->map(fn (User $user) => $this->serializeUser($user))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'prohibited',
            'site_ids' => 'required|array|min:1',
            'site_ids.*' => 'required|uuid|exists:sites,id',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'role' => User::ROLE_ADMIN,
            ]);

            $user->assignedSites()->sync($request->input('site_ids', []));

            return $user;
        });

        $user->load([
            'ownedSites:id,name,owner_id',
            'assignedSites:id,name',
        ]);

        return response()->json([
            'user' => $this->serializeUser($user),
        ], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'You cannot delete yourself.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    private function serializeUser(User $user): array
    {
        $sites = $this->mergeSites($user->ownedSites, $user->assignedSites)
            ->map(fn ($site) => [
                'id' => $site->id,
                'name' => $site->name,
            ])
            ->values()
            ->all();

        return array_merge($user->toArray(), [
            'sites' => $sites,
        ]);
    }

    private function mergeSites(EloquentCollection $ownedSites, EloquentCollection $assignedSites): EloquentCollection
    {
        return $ownedSites->concat($assignedSites)->unique('id');
    }
}
