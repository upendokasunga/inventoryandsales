<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPriceHistory extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $table = 'supplier_price_history';

    protected $fillable = [
        'supplier_id', 'product_id', 'product_unit_id', 'unit_price',
        'previous_price', 'price_change', 'effective_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'previous_price' => 'decimal:2',
            'price_change' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
