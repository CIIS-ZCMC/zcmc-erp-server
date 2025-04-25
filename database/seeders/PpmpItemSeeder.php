<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\AopApplication;
use App\Models\Division;
use App\Models\Item;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\PpmpSchedule;
use App\Models\ProcurementModes;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class PpmpItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $AopApplication = AopApplication::inRandomOrder()->first();
        $randomUser = User::inRandomOrder()->first();
        $DivisionChief = Division::where('name', 'Hospital Operations & Patient Support Service')->first();
        $BudgetOfficer = Section::where('name', 'FS: Budget Section')->first();

        $ppmp_application = PpmpApplication::create([
            'aop_application_id' => $AopApplication->id,
            'user_id' => $randomUser->id,
            'division_chief_id' => $DivisionChief->head_id,
            'budget_officer_id' => $BudgetOfficer->head_id,
            'ppmp_application_uuid' => Str::uuid(),
            'ppmp_total' => rand(10000, 1000000) / 100,
            'status' => 'submitted',
            'remarks' => 'Approved by all stakeholders'
        ]);

        $activity = Activity::inRandomOrder()->first();

        for ($i = 0; $i < 5; $i++) {
            $randomItem = Item::inRandomOrder()->first();
            $procurement = ProcurementModes::inRandomOrder()->first();
            $item_quantity = rand(5, 20);

            $ppmpItem = PpmpItem::create([
                'ppmp_application_id' => $ppmp_application->id,
                'item_id' => $randomItem->id,
                'procurement_mode_id' => $procurement->id,
                'item_request_id' => null,
                'total_quantity' => $item_quantity,
                'estimated_budget' => rand(10000, 100000),
                'total_amount' => 10 * $item_quantity,
                'remarks' => null
            ]);

            $activity->ppmpItems()->attach($ppmpItem->id, [
                'remarks' => null,
                'is_draft' => rand(0, 1),
            ]);

            for ($j = 0; $j < 12; $j++) {
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
