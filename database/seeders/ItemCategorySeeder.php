<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemCategory;
use Illuminate\Support\Str;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        ItemCategory::truncate();

        // Main categories to be seeded
        $mainCategories = [
            ['name' => 'Software and Subscription', 'code' => 'SAS'],
            ['name' => 'Equipment', 'code' => 'EQP'],
            ['name' => 'Services and Consultancy', 'code' => 'SAC'],
            ['name' => 'Training Expenses', 'code' => 'TRE'],
            ['name' => 'Furniture and Fixture', 'code' => 'FAF'],
            ['name' => 'Supply', 'code' => 'SUP'],
            ['name' => 'Instrument', 'code' => 'INS'],
            ['name' => 'Book, Journal, Publication', 'code' => 'BJP'],
            ['name' => 'Maintenance', 'code' => 'MNT'],
            ['name' => 'Parts and Spare', 'code' => 'PAS'],
            ['name' => 'Tax, License, Membership/Accreditation/Registration', 'code' => 'TLM'],
            ['name' => 'Subscription, Postage & Insurance', 'code' => 'SPI'],
            ['name' => 'Building and Construction', 'code' => 'BAC'],
            ['name' => 'Celebration and Events', 'code' => 'CAE'],
            ['name' => 'Instruments and Tools', 'code' => 'IAT'],
            ['name' => 'Implant', 'code' => 'IMP'],
        ];

        // First create all main categories
        $categoryMap = [];
        foreach ($mainCategories as $category) {
            $newCategory = ItemCategory::create([
                'name' => $category['name'],
                'code' => $category['code'],
                'description' => 'Main category for ' . $category['name'],
            ]);

            $categoryMap[$category['name']] = $newCategory->id;
        }

        // Sub-categories with their parent categories
        $subCategories = [
            // Equipment sub-categories
            ['parent' => 'Equipment', 'name' => 'ICT', 'code' => 'EQP-ICT'],
            ['parent' => 'Equipment', 'name' => 'Medical', 'code' => 'EQP-MED'],
            ['parent' => 'Equipment', 'name' => 'Other', 'code' => 'EQP-OTH'],
            ['parent' => 'Equipment', 'name' => 'Kitchen', 'code' => 'EQP-KIT'],
            
            // Supply sub-categories
            ['parent' => 'Supply', 'name' => 'Office', 'code' => 'SUP-OFF'],
            ['parent' => 'Supply', 'name' => 'Medical', 'code' => 'SUP-MED'],
            ['parent' => 'Supply', 'name' => 'Food', 'code' => 'SUP-FOD'],
            ['parent' => 'Supply', 'name' => 'Construction', 'code' => 'SUP-CON'],
            ['parent' => 'Supply', 'name' => 'Others', 'code' => 'SUP-OTH'],
            ['parent' => 'Supply', 'name' => 'Janitorial', 'code' => 'SUP-JAN'],
            ['parent' => 'Supply', 'name' => 'Equipment Maintenance', 'code' => 'SUP-EQM'],
            ['parent' => 'Supply', 'name' => 'Linen and Laundry', 'code' => 'SUP-LAL'],
            ['parent' => 'Supply', 'name' => 'Laboratory', 'code' => 'SUP-LAB'],
            
            // Instrument sub-categories
            ['parent' => 'Instrument', 'name' => 'Medical', 'code' => 'INS-MED'],
            
            // Maintenance sub-categories
            ['parent' => 'Maintenance', 'name' => 'Medical Equipment', 'code' => 'MNT-MED'],
            ['parent' => 'Maintenance', 'name' => 'Other Equipment', 'code' => 'MNT-OEQ'],
            ['parent' => 'Maintenance', 'name' => 'ICT', 'code' => 'MNT-ICT'],
        ];

        // Create all sub-categories
        foreach ($subCategories as $subCategory) {
            ItemCategory::create([
                'parent_id' => $categoryMap[$subCategory['parent']],
                'name' => $subCategory['name'],
                'code' => $subCategory['code'],
                'description' => 'Sub-category of ' . $subCategory['parent'],
            ]);
        }
    }
}
