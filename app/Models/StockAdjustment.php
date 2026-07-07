<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AutoHasUuid;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model implements Approvable
{
    use HasFactory, AutoHasUuid, SoftDeletes, HasApprovalWorkflow;

    public function getApprovalConfigKey(): string
    {
        return 'stock_adjustment';
    }

    public const TYPES = ['positive', 'negative', 'transfer', 'return'];
    public const REASONS = ['damaged', 'lost', 'found', 'expired', 'recount', 'theft', 'audit_count', 'correction', 'return', 'other'];
    public const STATUSES = ['draft', 'pending_approval', 'approved', 'completed', 'cancelled'];

    protected $fillable = [
        'adjustment_number', 'type', 'reason', 'description',
        'status', 'created_by', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
