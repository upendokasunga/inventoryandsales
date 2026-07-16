<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransfer extends Model
{
    use HasFactory, AutoHasUuid;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'source_account_id', 'destination_account_id', 'amount',
        'exchange_rate', 'converted_amount', 'source_currency', 'destination_currency',
        'description', 'reference', 'status', 'created_by', 'approved_by',
        'approved_at', 'journal_entry_id', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'converted_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
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
