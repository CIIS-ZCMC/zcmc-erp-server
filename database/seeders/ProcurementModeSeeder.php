<?php

namespace Database\Seeders;

use App\Models\ProcurementModes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcurementModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Procured',
            ],
            [
                'name' => 'Request',
            ],
        ];

        foreach ($data as $item) {
            ProcurementModes::create($item);
        }
    }
}
