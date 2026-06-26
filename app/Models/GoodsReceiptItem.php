<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceiptItem extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $fillable = [
        'goods_receipt_id', 'purchase_order_item_id', 'product_id',
        'expected_quantity', 'received_quantity', 'condition', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_quantity' => 'decimal:2',
            'received_quantity' => 'decimal:2',
        ];
    }

    public const CONDITIONS = ['good', 'damaged', 'partial', 'return'];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
