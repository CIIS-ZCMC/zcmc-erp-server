<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\TypeOfFunction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the type_of_function_id values
        // We'll need at least one TypeOfFunction to exist
        $typeOfFunctions = TypeOfFunction::all();
        
        if ($typeOfFunctions->isEmpty()) {
            // Create default type of functions if none exist
            $types = ['strategic', 'core', 'support'];
            
            foreach ($types as $type) {
                TypeOfFunction::create(['type' => $type]);
            }
            
            // Refresh the collection
            $typeOfFunctions = TypeOfFunction::all();
        }
        
        // Distribute objectives among type of functions
        $strategicTypeId = $typeOfFunctions->where('type', 'strategic')->first()->id ?? $typeOfFunctions->first()->id;
        $coreTypeId = $typeOfFunctions->where('type', 'core')->first()->id ?? $typeOfFunctions->first()->id;
        $supportTypeId = $typeOfFunctions->where('type', 'support')->first()->id ?? $typeOfFunctions->first()->id;
        
        $data = [
            [
                'type_of_function_id' => $strategicTypeId,
                'code' => 'OBJ-2023-001',
                'description' => 'Increase customer satisfaction ratings by 20% by the end of Q4 2023',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_of_function_id' => $strategicTypeId,
                'code' => 'OBJ-2023-002',
                'description' => 'Reduce operational costs by 15% through process optimization',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_of_function_id' => $coreTypeId,
                'code' => 'OBJ-2023-003',
                'description' => 'Expand market share in the Asia-Pacific region by 10%',
                'deleted_at' => null, // example of a soft-deleted objective
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_of_function_id' => $coreTypeId, 
                'code' => 'OBJ-2023-004',
                'description' => 'Launch three new product lines by Q2 2023',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_of_function_id' => $supportTypeId,
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
