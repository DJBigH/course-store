<?php

namespace Modules\User\src\Models;

use App\Notifications\AdminResetPasswordQueued;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'is_locked',
        'two_factor_email_enabled',
        'two_factor_email_enabled_at',
        'two_factor_email_code',
        'two_factor_email_code_expires_at',
        'two_factor_email_code_sent_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_browser',
        'last_login_platform',
        'last_login_device',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_email_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_locked' => 'boolean',
        'two_factor_email_enabled' => 'boolean',
        'two_factor_email_enabled_at' => 'datetime',
        'two_factor_email_code_expires_at' => 'datetime',
        'two_factor_email_code_sent_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
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

    public function isLocked(): bool
    {
        return (bool) ($this->is_locked ?? false);
    }

    public function canAccessAdmin(): bool
    {
        if ($this->isLocked()) {
            return false;
        }

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
                    'lessons.soft_delete',
                    'lessons.restore',
                    'lessons.force_delete',
                    'lessons.sort',
                    'categories.view',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',
                    'categories.soft_delete',
                    'categories.force_delete',
                    'categories.logs',
                    'categories.manage',
                    'teachers.view',
                    'teachers.create',
                    'teachers.edit',
                    'teachers.delete',
                    'teachers.soft_delete',
                    'teachers.force_delete',
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
                    'students.soft_delete',
                    'students.force_delete',
                    'students.logs',
                    'students.manage',
                    'coupons.manage',
                    'contacts.view',
                    'contacts.update',
                    'contacts.delete',
                    'contacts.soft_delete',
                    'contacts.force_delete',
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
            'chatbot.' => 'chatbot.manage',
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
