<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AutoHasUuid;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model implements Approvable
{
    use HasFactory, AutoHasUuid, SoftDeletes, HasApprovalWorkflow;

    public function getApprovalConfigKey(): string
    {
        return 'journal_entry';
    }

    public function getAllowedApprovalTransitions(): array
    {
        return [
            'draft' => ['pending_approval', 'posted'],
            'posted' => ['reversed'],
            'approved' => ['reversed'],
            'reversed' => [],
        ];
    }

    public function onApproved(): void
    {
        $totalDebit = $this->lines()->sum('debit');
        $totalCredit = $this->lines()->sum('credit');
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \InvalidArgumentException('Cannot approve an unbalanced journal entry.');
        }
    }

    public function getApprovedStatus(): string
    {
        return 'posted';
    }

    public const TYPES = ['general', 'adjustment', 'payment', 'receipt', 'contra', 'purchase', 'sales'];

    public const STATUSES = ['draft', 'posted', 'approved', 'reversed'];

    protected $fillable = [
        'entry_number', 'entry_date', 'type', 'status', 'description',
        'total_debit', 'total_credit',
        'reference_type', 'reference_id',
        'created_by', 'approved_by', 'approved_at',
        'reversed_by', 'reversed_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'approved_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePosted($query)
    {
        return $query->whereIn('status', ['posted', 'approved']);
    }
}
