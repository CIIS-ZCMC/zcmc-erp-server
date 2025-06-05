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
use Carbon\Carbon;
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
        $aop_application = AopApplication::inRandomOrder()->first();
        $random_user = User::inRandomOrder()->first();
        $division_chief = Division::where('name', 'Hospital Operations & Patient Support Service')->first();
        $budget_officer = Section::where('name', 'FS: Budget Section')->first();
        $planning_officer = Section::where('name', 'Planning Unit')->first();
        $expenseClasses = ['MOOE', 'CO', 'PS'];

        // Create PPMP for the random user
        $ppmp_application = PpmpApplication::create([
            'aop_application_id' => $aop_application->id,
            'user_id' => $random_user->id,
            'division_chief_id' => $division_chief->head_id,
            'budget_officer_id' => $budget_officer->head_id,
            'planning_officer_id' => $planning_officer->head_id,
            'ppmp_application_uuid' => Str::uuid(),
            'ppmp_total' => 0,
            'status' => 'submitted',
            'remarks' => "",
            'year' => Carbon::now()->format('Y'),
        ]);

        // Create PPMP for user_id 2384
        $ppmp_application_2384 = PpmpApplication::create([
            'aop_application_id' => $aop_application->id,
            'user_id' => 2384,
            'division_chief_id' => $division_chief->head_id,
            'budget_officer_id' => $budget_officer->head_id,
            'planning_officer_id' => $planning_officer->head_id,
            'ppmp_application_uuid' => Str::uuid(),
            'ppmp_total' => 0,
            'status' => 'submitted',
            'remarks' => "",
            'year' => Carbon::now()->format('Y'),
        ]);

        $items = Item::all();
        foreach ($items as $item) {
            $procurement = ProcurementModes::inRandomOrder()->first();
            // For random user
            $ppmpItem = PpmpItem::create([
                'ppmp_application_id' => $ppmp_application->id,
                'item_id' => $item->id,
                'procurement_mode_id' => $procurement->id,
                'item_request_id' => null,
                'total_quantity' => 0,
                'estimated_budget' => rand(10000, 100000),
                'total_amount' => 0,
                'expense_class' => $expenseClasses[array_rand($expenseClasses)],
                'remarks' => ""
            ]);

            $activities = Activity::inRandomOrder()->take(rand(1, 3))->get();
            foreach ($activities as $activity) {
                $activity->ppmpItems()->attach($ppmpItem->id, [
                    'remarks' => "",
                ]);
            }

            for ($j = 0; $j < 12; $j++) {
                PpmpSchedule::create([
                    'ppmp_item_id' => $ppmpItem->id,
                    'month' => rand(1, 12),
                    'year' => rand(2025, 2026),
                    'quantity' => 0
                ]);
            }

            // For user_id 2384
            $ppmpItem2384 = PpmpItem::create([
                'ppmp_application_id' => $ppmp_application_2384->id,
                'item_id' => $item->id,
                'procurement_mode_id' => $procurement->id,
                'item_request_id' => null,
                'total_quantity' => 0,
                'estimated_budget' => rand(10000, 100000),
                'total_amount' => 0,
                'expense_class' => $expenseClasses[array_rand($expenseClasses)],
                'remarks' => ""
            ]);

            foreach ($activities as $activity) {
                $activity->ppmpItems()->attach($ppmpItem2384->id, [
                    'remarks' => "",
                ]);
            }

            for ($j = 0; $j < 12; $j++) {
                PpmpSchedule::create([
                    'ppmp_item_id' => $ppmpItem2384->id,
                    'month' => rand(1, 12),
                    'year' => rand(2025, 2026),
                    'quantity' => 0
                ]);
            }
        }
    }
}
