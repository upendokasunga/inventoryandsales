<?php

namespace App\Models;

use Database\Factories\ProductUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    /** @use HasFactory<ProductUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id', 'unit_id', 'conversion_factor',
        'purchase_price', 'selling_price', 'wholesale_price', 'bulk_price',
        'is_default_sale', 'is_default_purchase', 'barcode',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:3',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'bulk_price' => 'decimal:2',
            'is_default_sale' => 'boolean',
            'is_default_purchase' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($unit) {
            if (is_null($unit->conversion_factor)) {
                $unit->conversion_factor = 1.000;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
