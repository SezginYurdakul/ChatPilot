<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function assignedSites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_user')
            ->withTimestamps();
    }

    public function accessibleSiteIds(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Site::query()->pluck('id');
        }

        return $this->assignedSites()->pluck('sites.id')
            ->values();
    }

    public function accessibleSitesQuery(): Builder
    {
        if ($this->isSuperAdmin()) {
            return Site::query();
        }

        return Site::query()->whereIn('id', $this->accessibleSiteIds());
    }
}
