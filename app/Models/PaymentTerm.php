<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'due_days', 'description', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'due_days' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('name');
    }
}
