<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN_NAME = 'Administrator';

    /**
     * Every permission an admin-area role can be granted, keyed by the
     * ability name used in routes (can:...) and views (@can).
     */
    public const PERMISSIONS = [
        'leads.view' => 'View leads and their documents',
        'leads.retry' => 'Restart processing of failed leads',
        'leads.delete' => 'Delete and restore leads',
        'affiliates.manage' => 'Create, edit and delete affiliates',
        'integrations.view' => 'View and test integrations',
        'horizon.view' => 'Open the Horizon queue monitor',
        'users.manage' => 'Manage users, roles and permissions',
    ];

    protected $fillable = ['name', 'permissions', 'is_admin'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_admin' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function admin(): self
    {
        return static::where('is_admin', true)->firstOrFail();
    }

    public function allows(string $permission): bool
    {
        return $this->is_admin || in_array($permission, $this->permissions ?? [], true);
    }
}
