<?php

namespace Modules\User\src\Models;

use App\Notifications\AdminResetPasswordQueued;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function isAdmin(): bool
    {
        if (!Schema::hasTable('groups')) {
            return (int) ($this->group_id ?? 0) === 1;
        }

        return $this->group?->slug === 'super_admin' || (int) ($this->group_id ?? 0) === 1;
    }

    public function canAccessAdmin(): bool
    {
        if (!Schema::hasTable('groups')) {
            return in_array((int) ($this->group_id ?? 0), [1, 2, 3], true);
        }

        if ($this->relationLoaded('group')) {
            return (bool) optional($this->group)->is_admin;
        }

        return (bool) optional($this->group()->first())->is_admin || $this->isAdmin();
    }

    public function canAnyPermission(array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $permissionSlug) {
            if ($this->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if (!Schema::hasTable('groups')) {
            $legacyGroupId = (int) ($this->group_id ?? 0);

            if ($legacyGroupId === 1) {
                return true;
            }

            $legacyMap = [
                2 => [
                    'dashboard.view',
                    'courses.view',
                    'courses.create',
                    'courses.edit',
                    'courses.publish',
                    'courses.soft_delete',
                    'courses.force_delete',
                    'lessons.view',
                    'lessons.create',
                    'lessons.edit',
                    'lessons.delete',
                    'lessons.sort',
                    'categories.view',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',
                    'categories.logs',
                    'categories.manage',
                    'teachers.view',
                    'teachers.create',
                    'teachers.edit',
                    'teachers.delete',
                    'teachers.logs',
                    'teachers.manage',
                    'comments.moderate',
                ],
                3 => [
                    'dashboard.view',
                    'orders.view',
                    'orders.update',
                    'students.view',
                    'students.create',
                    'students.edit',
                    'students.delete',
                    'students.logs',
                    'students.manage',
                    'coupons.manage',
                    'contacts.view',
                    'contacts.update',
                    'contacts.delete',
                    'contacts.logs',
                    'contacts.manage',
                ],
            ];

            return in_array($permissionSlug, $legacyMap[$legacyGroupId] ?? [], true);
        }

        if ($this->isAdmin()) {
            return true;
        }

        if ($this->relationLoaded('group') && $this->group) {
            $permissions = $this->group->relationLoaded('permissions')
                ? $this->group->permissions
                : $this->group->permissions()->get();

            if ($permissions->contains('slug', $permissionSlug)) {
                return true;
            }

            return $this->hasManageFallbackPermission($permissionSlug, $permissions->pluck('slug')->all());
        }

        $group = $this->group()->with('permissions')->first();

        if (!$group) {
            return false;
        }

        $permissionSlugs = $group->permissions->pluck('slug')->all();

        if (in_array($permissionSlug, $permissionSlugs, true)) {
            return true;
        }

        return $this->hasManageFallbackPermission($permissionSlug, $permissionSlugs);
    }

    public function scopeInGroup(Builder $query, string $slug): Builder
    {
        if (!Schema::hasTable('groups')) {
            if ($slug === 'super_admin') {
                return $query->where('group_id', 1);
            }

            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('group', function ($groupQuery) use ($slug) {
            $groupQuery->where('slug', $slug);
        });
    }

    public function scopeAdminPanelUsers(Builder $query): Builder
    {
        if (!Schema::hasTable('groups')) {
            return $query->whereIn('group_id', [1, 2, 3]);
        }

        return $query->whereHas('group', function ($groupQuery) {
            $groupQuery->where('is_admin', true);
        });
    }

    public function preferredLocale()
    {
        return app()->getLocale() ?: config('app.locale', 'vi');
    }

    protected function hasManageFallbackPermission(string $permissionSlug, array $permissionSlugs): bool
    {
        $manageFallbackMap = [
            'categories.' => 'categories.manage',
            'lessons.' => 'lessons.manage',
            'teachers.' => 'teachers.manage',
            'students.' => 'students.manage',
            'contacts.' => 'contacts.manage',
            'coupons.' => 'coupons.manage',
            'users.' => 'users.manage',
            'settings.' => 'settings.manage',
        ];

        foreach ($manageFallbackMap as $prefix => $manageSlug) {
            if (str_starts_with($permissionSlug, $prefix) && in_array($manageSlug, $permissionSlugs, true)) {
                return true;
            }
        }

        return false;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(
            (new AdminResetPasswordQueued($token))->locale($this->preferredLocale())
        );
    }
}
