<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    /**
     * Slugs that collide with application routes and can never be used as referral links.
     */
    public const RESERVED_SLUGS = ['admin', 'upload', 'php', 'up', 'horizon', 'login', 'logout', 'api', 'storage', 'build'];

    protected $fillable = ['name', 'slug', 'email', 'phone', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findActiveBySlug(?string $slug): ?self
    {
        if (blank($slug)) {
            return null;
        }

        return static::active()->where('slug', Str::lower($slug))->first();
    }

    public function link(): string
    {
        return url($this->slug);
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
