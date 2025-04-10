<?php

namespace Database\Seeders;

use App\Models\Objective;
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
        // First, check if we have objectives available
        $objectives = Objective::all();
        
        if ($objectives->isEmpty()) {
            $this->command->info('No objectives found. Please run the ObjectiveSeeder first.');
            return;
        }
        
        // Distribute success indicators among available objectives
        $objectiveCount = $objectives->count();
        $successIndicators = [
            [
                'code' => 'SI-2023-001',
                'description' => 'Achieve average customer satisfaction score of 4.5/5 in quarterly surveys',
            ],
            [
                'code' => 'SI-2023-002',
                'description' => 'Reduce departmental expenses by $50,000 compared to previous fiscal year',
            ],
            [
                'code' => 'SI-2023-003',
                'description' => 'Capture 5% additional market share in Singapore and Malaysia markets',
            ],
            [
                'code' => 'SI-2023-004',
                'description' => 'Launch all three products with at least 80% of planned features implemented',
            ],
            [
                'code' => 'SI-2023-005',
                'description' => 'Maintain employee turnover rate below 10% annually',
            ],
        ];
        
        // Assign each success indicator to an objective
        $data = [];
        foreach ($successIndicators as $index => $indicator) {
            // Distribute success indicators evenly across objectives
            $objectiveIndex = $index % $objectiveCount;
            $objectiveId = $objectives[$objectiveIndex]->id;
            
            $data[] = [
                'objective_id' => $objectiveId,
                'code' => $indicator['code'],
                'description' => $indicator['description'],
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        SuccessIndicator::insert($data);
    }
}
