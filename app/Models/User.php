<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) $this->role?->allows($permission);
    }

    /**
     * First admin page this user may open, so sign-in never lands on a 403.
     */
    public function homeUrl(): string
    {
        return match (true) {
            $this->hasPermission('leads.view') => route('admin.leads.index'),
            $this->hasPermission('affiliates.manage') => route('admin.affiliates.index'),
            $this->hasPermission('integrations.view') => route('admin.integrations'),
            $this->hasPermission('users.manage') => route('admin.users.index'),
            default => route('admin.account'),
        };
    }
}
