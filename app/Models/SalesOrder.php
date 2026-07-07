<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Services\CreditService;
use App\Traits\AutoHasUuid;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model implements Approvable
{
    use HasFactory, AutoHasUuid, SoftDeletes, HasApprovalWorkflow;

    public function getApprovalConfigKey(): string
    {
        return 'sales_order';
    }

    public const STATUSES = [
        'draft', 'pending_approval', 'approved', 'reserved',
        'picking', 'packed', 'partially_fulfilled', 'fulfilled',
        'invoiced', 'cancelled',
    ];

    protected $fillable = [
        'so_number', 'customer_id', 'price_list_id',
        'order_date', 'delivery_date', 'status',
        'payment_terms', 'subtotal', 'discount', 'discount_type',
        'tax', 'total', 'notes', 'internal_notes',
        'created_by', 'approved_by', 'approved_at',
        'reserved_by', 'reserved_at',
        'picked_by', 'picked_at',
        'packed_by', 'packed_at',
        'fulfilled_by', 'fulfilled_at',
        'invoiced_by', 'invoiced_at',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
            'reserved_at' => 'datetime',
            'picked_at' => 'datetime',
            'packed_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'invoiced_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reservist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function fulfiller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function onSubmitting(): void
    {
        if ($this->customer) {
            $creditCheck = app(CreditService::class)->validateCredit(
                $this->customer,
                $this->total
            );

            if (!$creditCheck['approved']) {
                throw new \InvalidArgumentException(
                    'Credit check failed: ' . ($creditCheck['reason'] ?? 'Unknown reason')
                );
            }
        }
    }
}
