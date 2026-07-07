<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankTransaction extends Model
{
    use HasFactory, AutoHasUuid;

    public const TYPES = ['deposit', 'withdrawal', 'transfer_in', 'transfer_out'];

    protected $fillable = [
        'uuid', 'bank_account_id', 'transaction_date', 'description',
        'reference_number', 'type', 'amount', 'running_balance',
        'reconciled', 'reference_type', 'reference_id', 'meta',
        'created_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'reconciled' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDeposits($query)
    {
        return $query->whereIn('type', ['deposit', 'transfer_in']);
    }

    public function scopeWithdrawals($query)
    {
        return $query->whereIn('type', ['withdrawal', 'transfer_out']);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }
}
