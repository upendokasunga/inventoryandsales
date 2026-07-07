<?php

namespace Database\Seeders;

use App\Models\DocumentNumberingConfig;
use Illuminate\Database\Seeder;

class DocumentNumberingConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['document_type' => 'purchase_order', 'prefix' => 'PO', 'separator' => '-', 'padding' => 6],
            ['document_type' => 'sales_order', 'prefix' => 'SO', 'separator' => '-', 'padding' => 6],
            ['document_type' => 'invoice', 'prefix' => 'INV', 'separator' => '-', 'padding' => 6],
            ['document_type' => 'goods_receipt', 'prefix' => 'GR', 'separator' => '-', 'padding' => 6],
            ['document_type' => 'credit_note', 'prefix' => 'CN', 'separator' => '-', 'padding' => 6],
            ['document_type' => 'sales_return', 'prefix' => 'SR', 'separator' => '-', 'padding' => 6],
            ['document_type' => 'purchase_return', 'prefix' => 'PR', 'separator' => '-', 'padding' => 6],
        ];

        foreach ($configs as $config) {
            DocumentNumberingConfig::firstOrCreate(
                ['document_type' => $config['document_type']],
                $config
            );
        }
    }
}
