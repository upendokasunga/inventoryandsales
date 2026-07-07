<?php

namespace App\Support;

class DocumentPrefixes
{
    const PREFIXES = [
        'invoice' => 'INV-',
        'proforma' => 'PRO-',
        'purchase_order' => 'PO-',
        'goods_receipt' => 'GRN-',
        'sales_return' => 'SR-',
        'credit_note' => 'CRN-',
        'product_code' => 'PRD-',
        'product_id' => 'PID-',
        'payment_voucher' => 'PV-',
        'supplier_payment' => 'SP-',
    ];

    public static function get(string $type): string
    {
        return self::PREFIXES[$type] ?? strtoupper($type) . '-';
    }

    public static function format(string $type, int $number, int $pad = 6): string
    {
        $prefix = self::get($type);
        return $prefix . str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
    }

    public static function formatWithYear(string $type, int $number, int $pad = 4): string
    {
        $prefix = self::get($type);
        return $prefix . now()->format('Y') . '-' . str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
    }
}
