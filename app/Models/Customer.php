<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use App\Traits\AutoLogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, AutoHasUuid, AutoLogsAudit, SoftDeletes;

    public const PAYMENT_TERMS = [
        'Cash', '7 Days', '14 Days', 'Net 30', 'Net 60', 'Net 90', 'Custom',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'city', 'region',
        'postal_code', 'country', 'contact_person', 'contact_phone', 'contact_email',
        'tax_id', 'registration_number', 'website',
        'customer_group_id',
        'credit_limit', 'available_credit', 'outstanding_balance',
        'payment_terms', 'credit_status', 'credit_hold_at', 'credit_hold_reason',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'available_credit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'credit_hold_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CustomerCreditTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCreditStatus($query, string $status)
    {
        return $query->where('credit_status', $status);
    }

    public function isOnHold(): bool
    {
        return $this->credit_status === 'suspended' || $this->credit_hold_at !== null;
    }
}
