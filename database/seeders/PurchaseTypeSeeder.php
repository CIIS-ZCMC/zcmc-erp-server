<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'description' => 'Procurable',
                'code' => 'PROC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Non-Procurable',
                'code' => 'NONPROC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Non-Procurable but needs budget',
                'code' => 'NONPROC-BUDGET',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('purchase_types')->insert($types);
    }
}
