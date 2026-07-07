<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberingConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type', 'prefix', 'separator', 'padding', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'padding' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
