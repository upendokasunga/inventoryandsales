<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBalance extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $fillable = [
        'product_id', 'quantity_on_hand', 'quantity_reserved',
        'quantity_available', 'quantity_incoming',
        'average_cost', 'total_value', 'last_transaction_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'quantity_reserved' => 'decimal:3',
            'quantity_available' => 'decimal:3',
            'quantity_incoming' => 'decimal:3',
            'average_cost' => 'decimal:2',
            'total_value' => 'decimal:2',
            'last_transaction_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
