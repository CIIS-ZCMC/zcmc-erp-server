<?php

namespace Database\Seeders;

use App\Models\Snomed;
use Illuminate\Database\Seeder;

class SnomedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $snomedCodes = [
            '123456789', // Example SNOMED codes
            '987654321',
            '1122334455',
            '5566778899',
            '1029384756',
            '0192837465',
            '765432198',
            '918273645',
            '182736455',
            '567890123'
        ];

        foreach ($snomedCodes as $code) {
            Snomed::create(['code' => $code]);
        }
    }
}
