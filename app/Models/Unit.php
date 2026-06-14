<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use App\Traits\AutoLogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, AutoHasUuid, AutoLogsAudit, SoftDeletes;

    protected $fillable = [
        'name', 'short_code', 'is_base',
    ];

    protected function casts(): array
    {
        return [
            'is_base' => 'boolean',
        ];
    }
}
