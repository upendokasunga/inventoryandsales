<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Models\SupplierPayment;
use App\Traits\AutoHasUuid;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model implements Approvable
{
    use HasFactory, AutoHasUuid, SoftDeletes, HasApprovalWorkflow;

    public function getApprovalConfigKey(): string
    {
        return 'purchase_order';
    }

    public function getAllowedApprovalTransitions(): array
    {
        return [
            'pending_approval' => ['approved', 'cancelled'],
            'approved' => ['cancelled', 'partially_received'],
            'partially_received' => ['completed'],
            'completed' => ['reversed'],
            'reversed' => [],
            'cancelled' => [],
        ];
    }

    protected $fillable = [
        'po_number', 'supplier_id', 'currency_code', 'exchange_rate',
        'order_date', 'expected_date',
        'status', 'subtotal', 'tax', 'discount', 'discount_type', 'total', 'total_amount', 'notes',
        'cost_center_id', 'created_by', 'approved_by', 'approved_at',
        'amount_paid', 'balance_due',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'exchange_rate' => 'decimal:8',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public const STATUSES = ['pending_approval', 'approved', 'partially_received', 'completed', 'reversed', 'cancelled'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function onApproved(): void
    {
        if ($this->supplier_id && ($this->total > 0 || $this->total_amount > 0)) {
            $poTotal = $this->total_amount ?: $this->total;

            $this->update([
                'balance_due' => $poTotal,
                'amount_paid' => 0,
            ]);

            $existing = SupplierPayment::where('purchase_order_id', $this->id)->first();
            if (!$existing) {
                SupplierPayment::create([
                    'purchase_order_id' => $this->id,
                    'supplier_id' => $this->supplier_id,
                    'amount' => $poTotal,
                    'status' => 'pending',
                    'payment_date' => $this->expected_date ?? now(),
                    'notes' => "Auto-generated from PO #{$this->po_number} approval",
                    'created_by' => $this->approved_by ?? auth()->id(),
                ]);
            }
        }
    }

    public function getRemainingBalanceAttribute(): float
    {
        return (float) ($this->total_amount ?: $this->total) - (float) $this->amount_paid;
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->amount_paid >= ($this->total_amount ?: $this->total);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }
}
