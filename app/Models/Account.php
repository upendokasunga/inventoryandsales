<?php

namespace App\Models;

use App\Helpers\AccountingHelper;
use App\Traits\AutoHasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, AutoHasUuid, SoftDeletes;

    public const TYPES = ['asset', 'liability', 'equity', 'income', 'expense'];

    protected $fillable = [
        'code', 'account_number', 'name', 'type', 'ifrs_category', 'category',
        'current_noncurrent', 'presentation_order', 'function_of_expense',
        'is_active', 'reportable', 'description',
        'opening_balance', 'current_balance', 'parent_id',
        'user_id', 'cost_center_id', 'branch_id', 'currency_code',
        'allow_overdraft', 'overdraft_limit',
        'bank_name', 'bank_swift_code', 'bank_branch',
        'include_in_income_statement',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'reportable' => 'boolean',
            'allow_overdraft' => 'boolean',
            'include_in_income_statement' => 'boolean',
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'overdraft_limit' => 'decimal:2',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeReportable($query)
    {
        return $query->where('reportable', true);
    }

    public function isCashOrBank(): bool
    {
        return AccountingHelper::isCashOrBankAccount($this);
    }
}
