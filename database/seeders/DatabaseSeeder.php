<?php

namespace Database\Seeders;

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
            VariantSeeder::class,
            SnomedSeeder::class,
            ItemCategorySeeder::class,
            TypeOfFunctionSeeder::class,
            ProcurementModeSeeder::class,
            ItemUnitSeeder::class,
            ObjectiveSeeder::class,
            SuccessIndicatorSeeder::class,
            ItemSpecificationSeeder::class,
            ItemSeeder::class,
//            PpmpItemSeeder::class,
            AopApplicationSeeder::class,
            ApplicationTimelineSeeder::class,
        ]);
    }
}
