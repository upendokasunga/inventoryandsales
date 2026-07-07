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
            ['key' => 'business_tin', 'value' => '', 'type' => 'string', 'description' => 'Tax Identification Number (TIN) for invoices'],
            ['key' => 'business_vat', 'value' => '', 'type' => 'string', 'description' => 'VAT registration number for invoices'],
            ['key' => 'business_logo', 'value' => '', 'type' => 'string', 'description' => 'URL or path to business logo for letterhead'],
            ['key' => 'business_terms', 'value' => 'Goods once sold will not be taken back. Payment must be made within 30 days from the date of invoice.', 'type' => 'text', 'description' => 'Default terms & conditions for printed documents'],
            ['key' => 'business_signatory_name', 'value' => '', 'type' => 'string', 'description' => 'Name of the authorized signatory for printed documents'],
            ['key' => 'business_signatory_title', 'value' => 'Managing Director', 'type' => 'string', 'description' => 'Title of the authorized signatory'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
