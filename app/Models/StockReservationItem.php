<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockReservationItem extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $fillable = [
        'stock_reservation_id', 'product_id',
        'inventory_batch_id', 'quantity', 'quantity_fulfilled',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'quantity_fulfilled' => 'decimal:3',
        ];
    }

    public function stockReservation(): BelongsTo
    {
        return $this->belongsTo(StockReservation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
