<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone1', 'phone2',
        'address', 'city', 'tax_id', 'payment_terms', 'notes',
        'bank_name', 'bank_branch', 'bank_swift_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
