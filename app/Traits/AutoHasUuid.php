<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait AutoHasUuid
{
    protected static function bootAutoHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
