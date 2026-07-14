<?php

namespace Database\Seeders;

use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentTermsSeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            ['name' => 'Cash on Delivery',  'code' => 'COD',    'due_days' => 0,  'is_active' => true, 'sort' => 1],
            ['name' => 'Net 7',             'code' => 'NET7',   'due_days' => 7,  'is_active' => true, 'sort' => 2],
            ['name' => 'Net 15',            'code' => 'NET15',  'due_days' => 15, 'is_active' => true, 'sort' => 3],
            ['name' => 'Net 30',            'code' => 'NET30',  'due_days' => 30, 'is_active' => true, 'sort' => 4],
            ['name' => 'Net 45',            'code' => 'NET45',  'due_days' => 45, 'is_active' => true, 'sort' => 5],
            ['name' => 'Net 60',            'code' => 'NET60',  'due_days' => 60, 'is_active' => true, 'sort' => 6],
            ['name' => 'Net 90',            'code' => 'NET90',  'due_days' => 90, 'is_active' => true, 'sort' => 7],
            ['name' => 'Immediate',         'code' => 'IMM',    'due_days' => 0,  'is_active' => true, 'sort' => 8],
        ];

        foreach ($terms as $term) {
            PaymentTerm::firstOrCreate(
                ['code' => $term['code']],
                $term
            );
        }
    }
}
