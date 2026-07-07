<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPerformance extends Model
{
    use HasFactory, AutoHasUuid;

    protected $table = 'supplier_performance';

    protected $fillable = [
        'supplier_id', 'total_orders', 'on_time_orders', 'late_orders',
        'on_time_rate', 'avg_lead_time_days', 'total_purchase_value',
        'total_items_received', 'damaged_items', 'returned_items',
        'order_accuracy_rate', 'quality_rate', 'return_rate', 'damage_rate',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_orders' => 'integer',
            'on_time_orders' => 'integer',
            'late_orders' => 'integer',
            'total_items_received' => 'integer',
            'damaged_items' => 'integer',
            'returned_items' => 'integer',
            'on_time_rate' => 'decimal:2',
            'avg_lead_time_days' => 'decimal:2',
            'total_purchase_value' => 'decimal:2',
            'order_accuracy_rate' => 'decimal:2',
            'quality_rate' => 'decimal:2',
            'return_rate' => 'decimal:2',
            'damage_rate' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
