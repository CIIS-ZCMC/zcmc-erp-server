<?php

namespace Database\Seeders;

use App\Models\SuccessIndicator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuccessIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'SI-2023-001',
                'description' => 'Achieve average customer satisfaction score of 4.5/5 in quarterly surveys',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SI-2023-002',
                'description' => 'Reduce departmental expenses by $50,000 compared to previous fiscal year',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SI-2023-003',
                'description' => 'Capture 5% additional market share in Singapore and Malaysia markets',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now() // example of a soft-deleted indicator
            ],
            [
                'code' => 'SI-2023-004',
                'description' => 'Launch all three products with at least 80% of planned features implemented',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SI-2023-005',
                'description' => 'Maintain employee turnover rate below 10% annually',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            ];

        SuccessIndicator::insert($data);
    }
}
