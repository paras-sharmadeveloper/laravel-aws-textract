<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key/value settings editable from the admin portal (e.g. "mail.host").
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Every setting under a prefix, keyed without it: group('mail') → ['host' => ...].
     */
    public static function group(string $prefix): array
    {
        return static::where('key', 'like', "{$prefix}.%")
            ->pluck('value', 'key')
            ->mapWithKeys(fn($value, $key) => [substr($key, strlen($prefix) + 1) => $value])
            ->all();
    }

    public static function putGroup(string $prefix, array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => "{$prefix}.{$key}"], ['value' => $value]);
        }
    }
}
