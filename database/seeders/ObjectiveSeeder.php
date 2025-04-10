<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\TypeOfFunction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    /**
     * Generate a random 4-digit number
     *
     * @return string
     */
    private function generateRandom4Digits()
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $strategic = TypeOfFunction::where("code",'like',"STRAT")->first();
        $core = TypeOfFunction::where('code', 'like','CORE')->first();
        $support = TypeOfFunction::where('code','like','SUPP')->first();

        $objectives = [
            // Strategic (type_of_function_id = 1)
            [
                'code' => 'OBJ-DOB-'. $this->generateRandom4Digits(),
                'description' => 'Disease outbreaks are prevented and/or managed',
                'type_of_function_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-HFS-'. $this->generateRandom4Digits(),
                'description' => 'Health facilities and services are safe and of quality',
                'type_of_function_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-NPC-'. $this->generateRandom4Digits(),
                'description' => 'Network of primary care and specialist care providers are high quality and well-distributed throughout the country',
                'type_of_function_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-AGH-'. $this->generateRandom4Digits(),
                'description' => 'All government health institutions are "right-sized" and efficient',
                'type_of_function_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Core (type_of_function_id = 2)
            [
                'code' => 'OBJ-TIH-'. $this->generateRandom4Digits(),
                'description' => 'To improve health service delivery through efficient hospital systems and operations',
                'type_of_function_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-TPS-'. $this->generateRandom4Digits(),
                'description' => 'To provide safe and quality care by reducing preventable hospital acquired infections',
                'type_of_function_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-TIM-'. $this->generateRandom4Digits(),
                'description' => 'To improve and monitor performance based on client experience',
                'type_of_function_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-TMD-'. $this->generateRandom4Digits(),
                'description' => 'To monitor disbursement of cash allocation',
                'type_of_function_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Support (type_of_function_id = 3)
            [
                'code' => 'OBJ-TEE-'. $this->generateRandom4Digits(),
                'description' => 'To ensure efficient utilization of DOH funds',
                'type_of_function_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-TIC-'. $this->generateRandom4Digits(),
                'description' => 'To increase capacity of all DOH personnel in order to improve workplace performance',
                'type_of_function_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-TEC-'. $this->generateRandom4Digits(),
                'description' => 'To ensure compliance with cross-cutting requirements based on standard procedures and timelines in accordance to Anti-Red Tape Authority (ARTA) and other relevant laws',
                'type_of_function_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-TET-'. $this->generateRandom4Digits(),
                'description' => 'To ensure the delivery of quality service though the provision of adequate human resource based on the approved standard staffing pattern',
                'type_of_function_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-EED-'. $this->generateRandom4Digits(),
                'description' => 'To ensure efficient delivery of goods/rendering of services/construction of infrastructure/civil works through the timely conduct of Early Procurement Activity (EPA)',
                'type_of_function_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBJ-O-'. $this->generateRandom4Digits(),
                'description' => 'Others, please insert note/remarks',
                'type_of_function_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        Objective::insert(values: $objectives);
    }
}
