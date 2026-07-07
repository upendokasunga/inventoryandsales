<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, AutoHasUuid;

    protected $table = 'sales_invoice_items';

    protected $fillable = [
        'uuid',
        'invoice_id',
        'product_id',
        'sub_product_id',
        'store_id',
        'product_unit_id',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function subProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sub_product_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'store_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'product_unit_id');
    }
}
