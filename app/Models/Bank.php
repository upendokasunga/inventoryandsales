<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'branch', 'swift_code', 'country', 'currency_code',
    ];

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }
}
