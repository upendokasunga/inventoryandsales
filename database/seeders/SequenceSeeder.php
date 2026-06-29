<?php

namespace Database\Seeders;

use App\Models\CreditNoteNumberSequence;
use App\Models\InvoiceNumberSequence;
use Illuminate\Database\Seeder;

class SequenceSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        InvoiceNumberSequence::firstOrCreate(
            ['year' => $year],
            ['last_number' => 0]
        );

        CreditNoteNumberSequence::firstOrCreate(
            ['year' => $year],
            ['last_number' => 0]
        );
    }
}
