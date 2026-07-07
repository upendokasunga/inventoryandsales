<?php

namespace Database\Factories;

use App\Models\DocumentNumberingConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentNumberingConfigFactory extends Factory
{
    protected $model = DocumentNumberingConfig::class;

    public function definition(): array
    {
        $types = ['purchase_order', 'sales_order', 'invoice', 'goods_receipt', 'credit_note', 'sales_return', 'purchase_return'];

        return [
            'document_type' => fake()->unique()->randomElement($types),
            'prefix' => fake()->unique()->randomElement(['PO', 'SO', 'INV', 'GR', 'CN', 'SR', 'PR']),
            'separator' => '-',
            'padding' => 6,
            'is_active' => true,
        ];
    }
}
