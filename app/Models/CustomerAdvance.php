<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomerAdvance extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const STATUSES = ['pending', 'completed', 'partially_applied', 'applied', 'cancelled'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'advance_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CustomerAdvance $advance) {
            if (!$advance->uuid) {
                $advance->uuid = (string) Str::uuid();
            }
            if (!$advance->advance_number) {
                $prefix = 'ADV-';
                $last = static::max('id') ?? 0;
                $advance->advance_number = $prefix . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
            if ($advance->balance === null) {
                $advance->balance = $advance->amount;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdvanceApplication::class, 'customer_advance_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partially_applied', 'completed']);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
