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
            ]);
        }
    }
}
