<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected string $cacheKey = 'app.settings';
    protected int $ttl = 3600;

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        $setting = $settings->firstWhere('key', $key);

        if (!$setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->type);
    }

    public function set(string $key, mixed $value, string $type = 'string', ?string $description = null): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );

        $this->flushCache();

        return $setting;
    }

    public function all()
    {
        $data = Cache::remember($this->cacheKey, $this->ttl, function () {
            return Setting::all()->toArray();
        });

        return Setting::hydrate($data);
    }

    public function has(string $key): bool
    {
        return $this->all()->contains('key', $key);
    }

    public function remove(string $key): bool
    {
        $result = Setting::where('key', $key)->delete();
        $this->flushCache();
        return (bool) $result;
    }

    public function flushCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    protected function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true) ?? explode(',', $value),
            default => $value,
        };
    }
}
