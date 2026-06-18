<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPriceHistory extends Model
{
    use HasFactory, AutoHasUuid;

    protected $fillable = [
        'supplier_id', 'product_id', 'unit_price',
        'currency', 'effective_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
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
}
