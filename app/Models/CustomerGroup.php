<?php

namespace App\Models;

use App\Traits\AutoHasUuid;
use App\Traits\AutoLogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    use HasFactory, AutoHasUuid, AutoLogsAudit;

    protected $fillable = [
        'name', 'description', 'default_credit_limit', 'default_payment_terms', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
