<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_request_id', 'product_id',
        'quantity_requested', 'quantity_issued', 'quantity_received',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:3',
            'quantity_issued' => 'decimal:3',
            'quantity_received' => 'decimal:3',
        ];
    }

    public function storeRequest(): BelongsTo
    {
        return $this->belongsTo(StoreRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
