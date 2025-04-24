<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Item;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\PpmpSchedule;
use App\Models\ProcurementModes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PpmpItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ppmp_applications = PpmpApplication::all();
        $activities = Activity::inRandomOrder()->first(); // Get one activity

        foreach ($ppmp_applications as $application) {
            $randomItem = Item::inRandomOrder()->first(); // ✅ Move inside loop for randomness
            $procurement = ProcurementModes::inRandomOrder()->first();
            $item_quantity = random_int(5, 20);

            // Create new PPMP item
            $ppmpItem = PpmpItem::create([
                'ppmp_application_id' => $application->id,
                'item_id' => $randomItem->id,
                'procurement_mode_id' => $procurement->id,
                'item_request_id' => null,
                'total_quantity' => $item_quantity,
                'estimated_budget' => rand(10000, 100000),
                'total_amount' => 10 * $item_quantity,
                'remarks' => null
            ]);

            // Attach item to activity via pivot table
            $activities->ppmpItems()->attach($ppmpItem->id, [
                'remarks' => null, // You were trying to use $item['remarks'], but $item doesn't exist
                'is_draft' => rand(0, 1),
            ]);

            // Generate 5 schedules per item
            for ($i = 0; $i < 5; $i++) {
                PpmpSchedule::create([
                    'ppmp_item_id' => $ppmpItem->id,
                    'month' => rand(1, 12),
                    'year' => rand(2025, 2026),
                    'quantity' => rand(1, $item_quantity),
                ]);
            }
        }
    }
}
