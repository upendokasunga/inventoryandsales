<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankInstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'CRDB Bank', 'branch' => 'Head Office', 'swift_code' => 'CORUTZTZ', 'country' => 'Tanzania', 'currency_code' => 'TZS'],
            ['name' => 'NMB Bank', 'branch' => 'Head Office', 'swift_code' => 'NMIBTZTZ', 'country' => 'Tanzania', 'currency_code' => 'TZS'],
            ['name' => 'NBC Bank', 'branch' => 'Head Office', 'swift_code' => 'NLCBTZTZ', 'country' => 'Tanzania', 'currency_code' => 'TZS'],
            ['name' => 'EXIM Bank', 'branch' => 'Head Office', 'swift_code' => 'EXTNTZTZ', 'country' => 'Tanzania', 'currency_code' => 'TZS'],
        ];

        foreach ($banks as $bank) {
            Bank::updateOrCreate(
                ['name' => $bank['name'], 'branch' => $bank['branch'], 'swift_code' => $bank['swift_code']],
                $bank
            );
        }
    }
}
