<?php

namespace Database\Seeders;

use App\Models\Objective;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'OBJ-2023-001',
                'description' => 'Increase customer satisfaction ratings by 20% by the end of Q4 2023',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-2023-002',
                'description' => 'Reduce operational costs by 15% through process optimization',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-2023-003',
                'description' => 'Expand market share in the Asia-Pacific region by 10%',
                'deleted_at' => null, // example of a soft-deleted objective
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-2023-004',
                'description' => 'Launch three new product lines by Q2 2023',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-2023-005',
                'description' => 'Improve employee retention rate to 90% through enhanced training programs',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]
            ];

        Objective::insert($data);
    }
}
