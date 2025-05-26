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
<<<<<<< HEAD
        $categories = [
            'Book, Journal, Publication',
            'Building and Construction',
            'Celebration and Events',
            'Construction Supply',
            'Drugs and Medicine',
            'Food Supply',
            'Furniture and Fixture',
            'ICT Equipment',
            'ICT Maintenance',
            'Implant',
            'Janitorial Supply',
            'Kitchen Equipment',
            'Laboratory Supply',
            'Linen and Laundry Supply',
            'Medical Equipment',
            'Medical Equipment Maintenance',
            'Medical Instrument',
            'Medical Supply',
            'Office Supply',
            'Other Equipment',
            'Other Equipment Maintenance',
            'Other Fees, Subscription, Processing',
            'Other Supply',
            'Other tools',
            'Parts and Spare',
            'Services and Consultancy',
            'Software and Subscription',
            'Training Expenses',
        ];

        foreach ($categories as $name) {
            DB::table('item_categories')->insertOrIgnore([
                'name' => $name,
                'code' => strtoupper(str_replace([' ', ',', '-'], '_', $name)),
                'description' => $name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
=======
        // Clear existing data to prevent duplicates
        // ItemCategory::truncate();

        // Main categories to be seeded
        $mainCategories = [
            ['name' => 'Software and Subscription', 'code' => 'SAS', 'reference_terminology_id' => 1],
            ['name' => 'Equipment', 'code' => 'EQP', 'reference_terminology_id' => 1],
            ['name' => 'Services and Consultancy', 'code' => 'SAC', 'reference_terminology_id' => 1],
            ['name' => 'Training Expenses', 'code' => 'TRE', 'reference_terminology_id' => 1],
            ['name' => 'Furniture and Fixture', 'code' => 'FAF', 'reference_terminology_id' => 1],
            ['name' => 'Supply', 'code' => 'SUP', 'reference_terminology_id' => 1],
            ['name' => 'Instrument', 'code' => 'INS', 'reference_terminology_id' => 1],
            ['name' => 'Book, Journal, Publication', 'code' => 'BJP', 'reference_terminology_id' => 1],
            ['name' => 'Maintenance', 'code' => 'MNT', 'reference_terminology_id' => 1],
            ['name' => 'Parts and Spare', 'code' => 'PAS', 'reference_terminology_id' => 1],
            ['name' => 'Tax, License, Membership/Accreditation/Registration', 'code' => 'TLM', 'reference_terminology_id' => 1],
            ['name' => 'Subscription, Postage & Insurance', 'code' => 'SPI', 'reference_terminology_id' => 1],
            ['name' => 'Building and Construction', 'code' => 'BAC', 'reference_terminology_id' => 1],
            ['name' => 'Celebration and Events', 'code' => 'CAE', 'reference_terminology_id' => 1],
            ['name' => 'Instruments and Tools', 'code' => 'IAT', 'reference_terminology_id' => 1],
            ['name' => 'Implant', 'code' => 'IMP', 'reference_terminology_id' => 1],
        ];

        // First create all main categories
        $categoryMap = [];
        foreach ($mainCategories as $category) {
            $newCategory = ItemCategory::create([
                'name' => $category['name'],
                'code' => $category['code'],
                'description' => 'Main category for ' . $category['name'],
                'reference_terminology_id' => $category['reference_terminology_id']
            ]);

            $categoryMap[$category['name']] = $newCategory->id;
        }

        // Sub-categories with their parent categories
        $subCategories = [
            // Equipment sub-categories
            ['parent' => 'Equipment', 'name' => 'ICT', 'code' => 'EQP-ICT', 'reference_terminology_id' => 1],
            ['parent' => 'Equipment', 'name' => 'Medical', 'code' => 'EQP-MED', 'reference_terminology_id' => 1],
            ['parent' => 'Equipment', 'name' => 'Other', 'code' => 'EQP-OTH', 'reference_terminology_id' => 1],
            ['parent' => 'Equipment', 'name' => 'Kitchen', 'code' => 'EQP-KIT', 'reference_terminology_id' => 1],
            
            // Supply sub-categories
            ['parent' => 'Supply', 'name' => 'Office', 'code' => 'SUP-OFF', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Medical', 'code' => 'SUP-MED', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Food', 'code' => 'SUP-FOD', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Construction', 'code' => 'SUP-CON', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Others', 'code' => 'SUP-OTH', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Janitorial', 'code' => 'SUP-JAN', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Equipment Maintenance', 'code' => 'SUP-EQM', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Linen and Laundry', 'code' => 'SUP-LAL', 'reference_terminology_id' => 1],
            ['parent' => 'Supply', 'name' => 'Laboratory', 'code' => 'SUP-LAB', 'reference_terminology_id' => 1],
            
            // Instrument sub-categories
            ['parent' => 'Instrument', 'name' => 'Medical', 'code' => 'INS-MED', 'reference_terminology_id' => 1],
            
            // Maintenance sub-categories
            ['parent' => 'Maintenance', 'name' => 'Medical Equipment', 'code' => 'MNT-MED', 'reference_terminology_id' => 1],
            ['parent' => 'Maintenance', 'name' => 'Other Equipment', 'code' => 'MNT-OEQ', 'reference_terminology_id' => 1],
            ['parent' => 'Maintenance', 'name' => 'ICT', 'code' => 'MNT-ICT', 'reference_terminology_id' => 1],
        ];

        // Create all sub-categories
        foreach ($subCategories as $subCategory) {
            ItemCategory::create([
                'item_category_id' => $categoryMap[$subCategory['parent']],
                'name' => $subCategory['name'],
                'code' => $subCategory['code'],
                'description' => 'Sub-category of ' . $subCategory['parent'],
                'reference_terminology_id' => $subCategory['reference_terminology_id']
>>>>>>> 95-item-standard-codes
            ]);
        }
    }
}
