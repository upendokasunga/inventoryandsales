<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use App\Traits\AutoLogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseSuggestion extends Model
{
    use HasFactory, AutoHasUuid, AutoLogsAudit, SoftDeletes;

    protected $fillable = [
        'product_id', 'supplier_id', 'suggested_quantity', 'reason',
        'status', 'notes', 'created_by', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'suggested_quantity' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public const STATUSES = ['pending', 'approved', 'rejected', 'converted'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
