<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceSheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'classification', 'current_balance', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfClassification($query, string $classification)
    {
        return $query->where('classification', $classification);
    }
}
