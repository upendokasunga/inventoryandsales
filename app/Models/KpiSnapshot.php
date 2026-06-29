<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Model;

class KpiSnapshot extends Model
{
    use AutoHasUuid;

    public $timestamps = false;

    protected $fillable = [
        'period', 'snapshot_date', 'metrics', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'metrics' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
