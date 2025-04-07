<?php

namespace Database\Seeders;

use App\Models\ObjectiveSuccessIndicator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObjectiveSuccessIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "objective_id" => 1,
                "success_indicator_id" => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                "objective_id" => 2,
                "success_indicator_id" => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                "objective_id" => 3,
                "success_indicator_id" => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                "objective_id" => 4,
                "success_indicator_id" => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                "objective_id" => 5,
                "success_indicator_id" => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        ObjectiveSuccessIndicator::insert($data);
    }
}
