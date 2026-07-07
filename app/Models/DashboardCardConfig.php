<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardCardConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'title', 'icon', 'color', 'section', 'sort_order', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }
}
