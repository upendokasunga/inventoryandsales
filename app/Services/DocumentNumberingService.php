<?php

namespace App\Services;

use App\Models\DocumentNumberingConfig;
use App\Models\CreditNoteNumberSequence;
use App\Models\GrNumberSequence;
use App\Models\InvoiceNumberSequence;
use App\Models\PoNumberSequence;
use App\Models\ReturnNumberSequence;
use App\Models\SoNumberSequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DocumentNumberingService
{
    protected string $cacheKey = 'document_numbering.configs';

    protected array $modelMap = [
        'purchase_order' => PoNumberSequence::class,
        'sales_order' => SoNumberSequence::class,
        'invoice' => InvoiceNumberSequence::class,
        'goods_receipt' => GrNumberSequence::class,
        'credit_note' => CreditNoteNumberSequence::class,
        'sales_return' => ReturnNumberSequence::class,
        'purchase_return' => ReturnNumberSequence::class,
    ];

    protected array $typeMap = [
        'sales_return' => 'SR',
        'purchase_return' => 'PR',
    ];

    public function getAllConfigs(): iterable
    {
        $cached = Cache::get($this->cacheKey);

        if ($cached !== null && is_array($cached)) {
            return collect(
                array_map(fn(array $item) => (object) $item, $cached)
            );
        }

        if ($cached !== null) {
            Cache::forget($this->cacheKey);
        }

        $configs = DocumentNumberingConfig::orderBy('document_type')->get();
        Cache::put($this->cacheKey, $configs->toArray(), 3600);

        return $configs;
    }

    public function getConfig(string $documentType): ?DocumentNumberingConfig
    {
        return DocumentNumberingConfig::where('document_type', $documentType)->first();
    }

    public function updateConfig(string $documentType, array $data): DocumentNumberingConfig
    {
        $config = DocumentNumberingConfig::updateOrCreate(
            ['document_type' => $documentType],
            [
                'prefix' => $data['prefix'] ?? $this->defaultPrefix($documentType),
                'separator' => $data['separator'] ?? '-',
                'padding' => $data['padding'] ?? 6,
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        $this->flushCache();
        return $config;
    }

    public function generateNumber(string $documentType, ?string $type = null): string
    {
        $config = $this->getConfig($documentType);

        if (!$config || !$config->is_active) {
            return $this->generateLegacy($documentType, $type);
        }

        $modelClass = $this->modelMap[$documentType] ?? null;
        if (!$modelClass) {
            throw new \InvalidArgumentException("Unknown document type: {$documentType}");
        }

        $year = now()->year;

        return DB::transaction(function () use ($modelClass, $year, $config, $documentType, $type) {
            $sequence = $modelClass::where('year', $year);

            if ($type && isset($this->typeMap[$documentType])) {
                $sequence = $sequence->where('type', $type);
            }

            $sequence = $sequence->first();

            if (!$sequence) {
                $data = ['year' => $year, 'last_number' => 1];
                if ($type && isset($this->typeMap[$documentType])) {
                    $data['type'] = $type;
                }
                $sequence = $modelClass::create($data);
            } else {
                $sequence->increment('last_number');
                $sequence->refresh();
            }

            $num = str_pad((string) $sequence->last_number, $config->padding, '0', STR_PAD_LEFT);
            $typeCode = $this->typeMap[$documentType] ?? null;

            if ($type && $typeCode) {
                return "{$config->prefix}{$config->separator}{$year}{$config->separator}{$typeCode}{$config->separator}{$num}";
            }

            return "{$config->prefix}{$config->separator}{$year}{$config->separator}{$num}";
        });
    }

    public function generateLegacy(string $documentType, ?string $type = null): string
    {
        $modelClass = $this->modelMap[$documentType] ?? null;
        if (!$modelClass) {
            throw new \InvalidArgumentException("Unknown document type: {$documentType}");
        }

        $year = now()->year;

        return DB::transaction(function () use ($modelClass, $year, $documentType, $type) {
            $query = $modelClass::where('year', $year);

            if ($type && isset($this->typeMap[$documentType])) {
                $query = $query->where('type', $type);
            }

            $sequence = $query->first();

            if (!$sequence) {
                $data = ['year' => $year, 'last_number' => 1];
                if ($type && isset($this->typeMap[$documentType])) {
                    $data['type'] = $type;
                }
                $sequence = $modelClass::create($data);
            } else {
                $sequence->increment('last_number');
                $sequence->refresh();
            }

            $prefixes = [
                'purchase_order' => 'PO',
                'sales_order' => 'SO',
                'invoice' => 'INV',
                'goods_receipt' => 'GR',
                'credit_note' => 'CN',
                'sales_return' => 'SR',
                'purchase_return' => 'PR',
            ];

            $prefix = $prefixes[$documentType] ?? 'DOC';
            $num = str_pad((string) $sequence->last_number, 6, '0', STR_PAD_LEFT);

            if ($type && isset($this->typeMap[$documentType])) {
                $code = $this->typeMap[$documentType];
                return "{$prefix}-{$year}-{$code}-{$num}";
            }

            return "{$prefix}-{$year}-{$num}";
        });
    }

    protected function defaultPrefix(string $documentType): string
    {
        return match ($documentType) {
            'purchase_order' => 'PO',
            'sales_order' => 'SO',
            'invoice' => 'INV',
            'goods_receipt' => 'GR',
            'credit_note' => 'CN',
            'sales_return' => 'SR',
            'purchase_return' => 'PR',
            default => 'DOC',
        };
    }

    public function flushCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
