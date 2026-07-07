<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AutoHasUuid;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model implements Approvable
{
    use HasFactory, AutoHasUuid, SoftDeletes, HasApprovalWorkflow;

    public function getApprovalConfigKey(): string
    {
        return 'invoice';
    }

    public function getAllowedApprovalTransitions(): array
    {
        return [
            'draft' => ['proforma', 'pending_approval', 'cancelled'],
            'proforma' => ['pending_approval', 'draft', 'cancelled'],
            'pending_approval' => ['approved', 'draft', 'cancelled'],
            'approved' => ['posted', 'cancelled'],
            'posted' => ['cancelled', 'reversed'],
            'completed' => ['reversed'],
            'cancelled' => [],
            'reversed' => [],
        ];
    }

    protected $table = 'sales_invoices';

    public const STATUSES = ['draft', 'proforma', 'pending_approval', 'approved', 'posted', 'completed', 'cancelled', 'reversed'];

    public const PAYMENT_STATUSES = ['pending', 'partial', 'paid', 'overdue', 'cancelled'];

    public const PAYMENT_TYPES = ['cash', 'credit', 'bank_transfer', 'mobile_money', 'cheque', 'mixed'];

    protected $fillable = [
        'uuid',
        'invoice_number',
        'customer_id',
        'sales_order_id',
        'invoice_date',
        'payment_type',
        'payment_status',
        'subtotal',
        'discount',
        'discount_type',
        'tax',
        'total',
        'amount_paid',
        'balance_due',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'approved_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function advanceApplications(): HasMany
    {
        return $this->hasMany(\App\Models\AdvanceApplication::class, 'invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue');
    }
}
