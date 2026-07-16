<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $table = 'sales_returns';

    public const STATUSES = ['pending_approval', 'approved', 'rejected', 'completed'];

    public const REASONS = [
        'damaged',
        'wrong_item',
        'expired',
        'customer_dissatisfaction',
        'pricing_error',
        'duplicate_order',
    ];

    protected $fillable = [
        'uuid',
        'return_number',
        'invoice_id',
        'customer_id',
        'total_amount',
        'reason',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function creditNote(): HasMany
    {
        return $this->hasMany(CreditNote::class, 'sales_return_id');
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
