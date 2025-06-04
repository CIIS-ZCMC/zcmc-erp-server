<?php

namespace Database\Seeders;

use App\Models\ItemClassification;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ItemReferenceTerminologiesSeeder::class,
            ItemClassificationSeeder::class,
            ItemCategorySeeder::class,
            PurchaseTypeSeeder::class,
            TerminologyCategorySeeder::class,
            TypeOfFunctionSeeder::class,
            ProcurementModeSeeder::class,
            AopApplicationSeeder::class,
            ItemUnitSeeder::class,
            ItemClassificationSeeder::class,
            ObjectiveSeeder::class,
            SuccessIndicatorSeeder::class,
            ItemSpecificationSeeder::class,
            ItemSeeder::class,
            PpmpItemSeeder::class,
            PpmpApplicationSeeder::class,
            ApplicationTimelineSeeder::class,


        ]);
    }
}
