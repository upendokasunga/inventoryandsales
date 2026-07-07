<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalTracking extends Model
{
    protected $table = 'approval_tracking';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'approval_configuration_id',
        'current_level',
        'required_levels',
        'status',
        'submitted_at',
        'submitted_by',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'required_levels' => 'integer',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ApprovalConfiguration::class, 'approval_configuration_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function log(): HasMany
    {
        return $this->hasMany(ApprovalTrackingLog::class);
    }

    public function isFullyApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function needsMoreApprovals(): bool
    {
        return $this->current_level < $this->required_levels;
    }

    public function advanceLevel(): void
    {
        $this->increment('current_level');

        if (!$this->needsMoreApprovals()) {
            $this->update([
                'status' => 'approved',
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);
        }
    }
}
