<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_configuration_id', 'level', 'name', 'group_id', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ApprovalConfiguration::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
