<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    public const TYPES = [
        'purchase_receipt', 'sales_order', 'adjustment', 'transfer',
        'return', 'initial', 'sale_return', 'purchase_return',
        'damage', 'expiry', 'reservation', 'reservation_release',
    ];

    protected $fillable = [
        'product_id', 'warehouse_id', 'reference_type', 'reference_id',
        'type', 'quantity', 'unit_cost', 'total_cost',
        'balance_before', 'balance_after', 'description', 'created_by',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'balance_before' => 'decimal:3',
            'balance_after' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
