<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneyTransfer extends Model
{
    use HasFactory, AutoHasUuid;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'from_account_id', 'to_account_id', 'amount', 'reference',
        'description', 'status', 'created_by', 'approved_by', 'approved_at',
        'journal_entry_id', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
