<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'business_name', 'value' => 'WholesaleTZ', 'type' => 'string', 'description' => 'Business name for invoices and receipts'],
            ['key' => 'business_address', 'value' => '123 Maktaba Street, Dar es Salaam', 'type' => 'string', 'description' => 'Business address for invoices and receipts'],
            ['key' => 'business_phone', 'value' => '+255 123 456 789', 'type' => 'string', 'description' => 'Business phone number for invoices and receipts'],
            ['key' => 'business_email', 'value' => 'info@wholesaletZ.com', 'type' => 'string', 'description' => 'Business email for invoices and receipts'],
            ['key' => 'tax_rate', 'value' => '0', 'type' => 'float', 'description' => 'Default tax rate percentage for POS (e.g., 18 for 18%)'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
