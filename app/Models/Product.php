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
        'product_code', 'product_id', 'product_type',
        'price', 'retail_price', 'standard_cost', 'costing_method',
        'income_account_id', 'cost_center', 'brand_code',
        'expiry_date', 'unit', 'category',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'reorder_level' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'safety_stock' => 'decimal:3',
            'weight' => 'decimal:3',
            'price' => 'decimal:2',
            'retail_price' => 'decimal:2',
            'standard_cost' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            if (empty($product->product_code)) {
                $maxCode = static::whereNotNull('product_code')
                    ->where('product_code', 'like', 'PRD-%')
                    ->max('product_code');
                $nextNum = $maxCode ? (int) str_replace('PRD-', '', $maxCode) + 1 : 1;
                $product->product_code = 'PRD-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            }

            if (empty($product->product_id)) {
                $maxId = static::whereNotNull('product_id')
                    ->where('product_id', 'like', 'PID-%')
                    ->max('product_id');
                $nextNum = $maxId ? (int) str_replace('PID-', '', $maxId) + 1 : 1;
                $product->product_id = 'PID-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    public function scopeGoods($query)
    {
        return $query->where('product_type', 'goods');
    }

    public function scopeServices($query)
    {
        return $query->where('product_type', 'service');
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
