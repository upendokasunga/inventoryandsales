<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'completed', 'cancelled'];

    protected $fillable = [
        'reconciliation_number', 'bank_account_id', 'start_date', 'end_date',
        'opening_balance', 'closing_balance', 'statement_reference',
        'status', 'difference', 'created_by', 'completed_by', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BankReconciliation $rec) {
            if (!$rec->reconciliation_number) {
                $prefix = 'REC-';
                $last = static::max('id') ?? 0;
                $rec->reconciliation_number = $prefix . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class, 'bank_reconciliation_id');
    }

    public function transactions()
    {
        return $this->belongsToMany(BankTransaction::class, 'bank_reconciliation_items', 'bank_reconciliation_id', 'bank_transaction_id')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
