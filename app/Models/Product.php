<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'barcode', 'barcode_image',
        'description', 'tax_rate', 'tax_inclusive', 'is_active',
        'track_stock', 'reorder_level', 'current_stock', 'safety_stock', 'image', 'weight',
        'parent_product_id', 'has_variants', 'variant_attributes',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'has_variants' => 'boolean',
            'reorder_level' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'safety_stock' => 'decimal:3',
            'weight' => 'decimal:3',
            'variant_attributes' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_product_id');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_product_id');
    }

    public function scopeVariants($query)
    {
        return $query->whereNotNull('parent_product_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
