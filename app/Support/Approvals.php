<?php

namespace App\Support;

use App\Models\ApprovalConfiguration;
use Illuminate\Support\Facades\Cache;

class Approvals
{
    const CACHE_KEY = 'approval_level';
    const CACHE_TTL = 3600;

    public static function level(string $entity): int
    {
        return (int) Cache::remember(self::CACHE_KEY . ".{$entity}", self::CACHE_TTL, function () use ($entity) {
            $config = ApprovalConfiguration::where('module_key', $entity)->where('is_active', true)->first();
            return $config ? (int) $config->approval_level : 0;
        });
    }

    public static function requiresApproval(string $entity): bool
    {
        return self::level($entity) > 0;
    }

    public static function isLevelZero(string $entity): bool
    {
        return self::level($entity) === 0;
    }

    public static function maxSteps(string $entity): int
    {
        return max(1, self::level($entity));
    }

    public static function flush(string $entity = null): void
    {
        if ($entity) {
            Cache::forget(self::CACHE_KEY . ".{$entity}");
        } else {
            $entities = ['purchase_orders', 'invoices', 'goods_receipts', 'sales_returns', 'journal_entries'];
            foreach ($entities as $e) {
                Cache::forget(self::CACHE_KEY . ".{$e}");
            }
        }
    }
}
