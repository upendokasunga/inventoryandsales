<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_key', 'module_name', 'approval_level', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'approval_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalLevel::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey);
    }

    public function requiresApproval(): bool
    {
        return $this->approval_level > 0 && $this->is_active;
    }
}
