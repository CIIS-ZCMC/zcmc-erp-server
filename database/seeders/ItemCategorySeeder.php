<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        // ItemCategory::truncate();

        // Main categories to be seeded
        $mainCategories = [
            ['name' => 'Book, Journal, Publication', 'code' => 'BJP'],
            ['name' => 'Building and Construction', 'code' => 'BAC'],
            ['name' => 'Celebration and Events', 'code' => 'CAE'],
            ['name' => 'Construction Supply', 'code' => 'COS'],
            ['name' => 'Drugs and Medicine', 'code' => 'DAM'],
            ['name' => 'Food Supply', 'code' => 'FOS'],
            ['name' => 'Furniture and Fixture', 'code' => 'FAF'],
            ['name' => 'ICT Equipment', 'code' => 'ICT'],
            ['name' => 'ICT Maintenance', 'code' => 'ICM'],
            ['name' => 'Implant', 'code' => 'IMP'],
            ['name' => 'Janitorial Supply', 'code' => 'JAS'],
            ['name' => 'Kitchen Equipment', 'code' => 'KIE'],
            ['name' => 'Laboratory Supply', 'code' => 'LAS'],
            ['name' => 'Linen and Laundry Supply', 'code' => 'LLS'],
            ['name' => 'Medical Equipment', 'code' => 'MEE'],
            ['name' => 'Medical Equipment Maintenance', 'code' => 'MEM'],
            ['name' => 'Medical Instrument', 'code' => 'MIN'],
            ['name' => 'Medical Supply', 'code' => 'MES'],
            ['name' => 'Office Supply', 'code' => 'OFS'],
            ['name' => 'Other Equipment', 'code' => 'OTE'],
            ['name' => 'Other Equipment Maintenance', 'code' => 'OEM'],
            ['name' => 'Other Fees, Subscription, Processing', 'code' => 'OFS'],
            ['name' => 'Other Supply', 'code' => 'OTS'],
            ['name' => 'Other tools', 'code' => 'OTT'],
            ['name' => 'Parts and Spare', 'code' => 'PAS'],
            ['name' => 'Services and Consultancy', 'code' => 'SAC'],
            ['name' => 'Software and Subscription', 'code' => 'SAS'],
            ['name' => 'Training Expenses', 'code' => 'TRE'],
        ];

        foreach ($mainCategories as $category) {
             ItemCategory::create([
                'name' => $category['name'],
                'code' => $category['code'],
                'description' => 'Main category for ' . $category['name']
            ]);

        }
    }
}
