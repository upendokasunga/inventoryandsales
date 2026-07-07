<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $fillable = [
        'name', 'route', 'icon', 'module', 'sort_order', 'is_active',
        'parent_id', 'is_parent', 'is_visible', 'section',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_parent' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_menu')
            ->withPivot('can_view', 'can_create', 'can_edit', 'can_delete', 'can_approve', 'can_2fa', 'can_print', 'can_export', 'can_import', 'can_reverse', 'can_cancel')
            ->withTimestamps();
    }
}
