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
        // ItemCategory::truncate();

        // Main categories to be seeded
        $mainCategories = [
            ['name' => 'Software and Subscription', 'code' => 'SAS', 'item_reference_terminology_id' => 1],
            ['name' => 'Equipment', 'code' => 'EQP', 'item_reference_terminology_id' => 1],
            ['name' => 'Services and Consultancy', 'code' => 'SAC', 'item_reference_terminology_id' => 1],
            ['name' => 'Training Expenses', 'code' => 'TRE', 'item_reference_terminology_id' => 1],
            ['name' => 'Furniture and Fixture', 'code' => 'FAF', 'item_reference_terminology_id' => 1],
            ['name' => 'Supply', 'code' => 'SUP', 'item_reference_terminology_id' => 1],
            ['name' => 'Instrument', 'code' => 'INS', 'item_reference_terminology_id' => 1],
            ['name' => 'Book, Journal, Publication', 'code' => 'BJP', 'item_reference_terminology_id' => 1],
            ['name' => 'Maintenance', 'code' => 'MNT', 'item_reference_terminology_id' => 1],
            ['name' => 'Parts and Spare', 'code' => 'PAS', 'item_reference_terminology_id' => 1],
            ['name' => 'Tax, License, Membership/Accreditation/Registration', 'code' => 'TLM', 'item_reference_terminology_id' => 1],
            ['name' => 'Subscription, Postage & Insurance', 'code' => 'SPI', 'item_reference_terminology_id' => 1],
            ['name' => 'Building and Construction', 'code' => 'BAC', 'item_reference_terminology_id' => 1],
            ['name' => 'Celebration and Events', 'code' => 'CAE', 'item_reference_terminology_id' => 1],
            ['name' => 'Instruments and Tools', 'code' => 'IAT', 'item_reference_terminology_id' => 1],
            ['name' => 'Implant', 'code' => 'IMP', 'item_reference_terminology_id' => 1],
        ];

        // First create all main categories
        $categoryMap = [];
        foreach ($mainCategories as $category) {
            $newCategory = ItemCategory::create([
                'name' => $category['name'],
                'code' => $category['code'],
                'description' => 'Main category for ' . $category['name'],
                'item_reference_terminology_id' => $category['item_reference_terminology_id']
            ]);

            $categoryMap[$category['name']] = $newCategory->id;
        }

        // Sub-categories with their parent categories
        $subCategories = [
            // Equipment sub-categories
            ['parent' => 'Equipment', 'name' => 'ICT', 'code' => 'EQP-ICT', 'item_reference_terminology_id' => 1],
            ['parent' => 'Equipment', 'name' => 'Medical', 'code' => 'EQP-MED', 'item_reference_terminology_id' => 1],
            ['parent' => 'Equipment', 'name' => 'Other', 'code' => 'EQP-OTH', 'item_reference_terminology_id' => 1],
            ['parent' => 'Equipment', 'name' => 'Kitchen', 'code' => 'EQP-KIT', 'item_reference_terminology_id' => 1],
            
            // Supply sub-categories
            ['parent' => 'Supply', 'name' => 'Office', 'code' => 'SUP-OFF', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Medical', 'code' => 'SUP-MED', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Food', 'code' => 'SUP-FOD', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Construction', 'code' => 'SUP-CON', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Others', 'code' => 'SUP-OTH', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Janitorial', 'code' => 'SUP-JAN', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Equipment Maintenance', 'code' => 'SUP-EQM', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Linen and Laundry', 'code' => 'SUP-LAL', 'item_reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Laboratory', 'code' => 'SUP-LAB', 'item_reference_terminology_id' => 1],
            
            // Instrument sub-categories
            ['parent' => 'Instrument', 'name' => 'Medical', 'code' => 'INS-MED', 'item_reference_terminology_id' => 1],
            
            // Maintenance sub-categories
            ['parent' => 'Maintenance', 'name' => 'Medical Equipment', 'code' => 'MNT-MED', 'item_reference_terminology_id' => 1],
            ['parent' => 'Maintenance', 'name' => 'Other Equipment', 'code' => 'MNT-OEQ', 'item_reference_terminology_id' => 1],
            ['parent' => 'Maintenance', 'name' => 'ICT', 'code' => 'MNT-ICT'],
        ];

        // Create all sub-categories
        foreach ($subCategories as $subCategory) {
            ItemCategory::create([
                'item_category_id' => $categoryMap[$subCategory['parent']],
                'name' => $subCategory['name'],
                'code' => $subCategory['code'],
                'description' => 'Sub-category of ' . $subCategory['parent'],
                'item_reference_terminology_id' => $subCategory['item_reference_terminology_id']
            ]);
        }
    }
}
