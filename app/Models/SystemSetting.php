<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Retrieve setting value by key with optional default fallback.
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("sys_setting_{$key}", 60, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set or update setting value.
     */
    public static function set(string $key, $value, ?string $type = null, ?string $description = null): self
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = (string)$value;
        if ($type) {
            $setting->type = $type;
        }
        if ($description) {
            $setting->description = $description;
        }
        $setting->save();

        Cache::forget("sys_setting_{$key}");
        Cache::forget('sys_settings_assoc');

        return $setting;
    }

    /**
     * Get all settings as key => typed_value associative array.
     */
    public static function getAllAssoc(): array
    {
        return Cache::remember('sys_settings_assoc', 60, function () {
            $settings = self::all();
            $assoc = [];
            foreach ($settings as $s) {
                $assoc[$s->key] = self::castValue($s->value, $s->type);
            }
            return $assoc;
        });
    }

    /**
     * Cast string representation to native PHP type.
     */
    protected static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'integer':
            case 'int':
                return (int)$value;
            case 'decimal':
            case 'float':
                return (float)$value;
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            default:
                return (string)$value;
        }
    }
}

