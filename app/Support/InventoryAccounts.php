<?php

namespace App\Support;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class InventoryAccounts
{
    const CACHE_KEY = 'inventory_accounts';
    const CACHE_TTL = 3600;

    public static function inventory(): ?Account
    {
        return self::resolve('1300');
    }

    public static function accountsPayable(): ?Account
    {
        return self::resolve('2100');
    }

    public static function accountsReceivable(): ?Account
    {
        return self::resolve('1200');
    }

    public static function salesDiscounts(): ?Account
    {
        return self::resolve('4050');
    }

    public static function cogs(): ?Account
    {
        return self::resolve('5100');
    }

    public static function salesRevenue(): ?Account
    {
        return self::resolve('4000');
    }

    public static function vatOutput(): ?Account
    {
        return self::resolve('2100');
    }

    public static function bank(): ?Account
    {
        return self::resolve('1100');
    }

    public static function resolve(string $code): ?Account
    {
        return Cache::remember(self::CACHE_KEY . ".{$code}", self::CACHE_TTL, function () use ($code) {
            return Account::where('code', $code)->first();
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY . '.1300');
        Cache::forget(self::CACHE_KEY . '.2100');
        Cache::forget(self::CACHE_KEY . '.1200');
        Cache::forget(self::CACHE_KEY . '.4050');
        Cache::forget(self::CACHE_KEY . '.5100');
        Cache::forget(self::CACHE_KEY . '.4000');
        Cache::forget(self::CACHE_KEY . '.1100');
    }
}
