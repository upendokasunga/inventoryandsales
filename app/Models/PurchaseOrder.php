<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use App\Traits\AutoLogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, AutoHasUuid, AutoLogsAudit, SoftDeletes;

    protected $fillable = [
        'po_number', 'supplier_id', 'order_date', 'expected_date',
        'status', 'subtotal', 'tax', 'total', 'notes',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public const STATUSES = ['draft', 'pending_approval', 'approved', 'sent', 'partially_received', 'completed', 'cancelled'];

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

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }
}
