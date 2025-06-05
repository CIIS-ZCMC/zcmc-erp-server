<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use App\Models\ItemReferenceTerminology;
use App\Models\TerminologyCategory;
use Illuminate\Database\Seeder;
use Log;

class TerminologyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ict_details = [
            ['category' => 'ICT Equipment', 'code' => 'Regular'],
            ['category' => 'ICT Equipment', 'code' => 'Mid-range'],
            ['category' => 'ICT Equipment', 'code' => 'High-End'],
            ['category' => 'Software and Subscription', 'code' => 'Online'],
            ['category' => 'Software and Subscription', 'code' => 'Regular'],
            ['category' => 'Software and Subscription', 'code' => 'Enterprise'],
            ['category' => 'Office Supply', 'code' => 'Regular'],
            ['category' => 'Office Supply', 'code' => 'High-end'],
            ['category' => 'Office Supply', 'code' => 'Mid-range']
        ];

        $ict = [];

        foreach($ict_details as $item){
            $category = ItemCategory::where('name', $item['category'])->first();
            $item_referrence_terminology = ItemReferenceTerminology::where('code', $item['code'])->first();

            $ict[] = [
                'name' => $item_referrence_terminology['system'].'-'.$item_referrence_terminology['code'],
                'reference_terminology_id' => $item_referrence_terminology->id,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $furniture_details = [
            ['category' => 'Furniture and Fixture', 'code' => 'Regular'],
            ['category' => 'Furniture and Fixture', 'code' => 'High-end'],
            ['category' => 'Furniture and Fixture', 'code' => 'Mid-range'],
        ];

        $furnitures = [];

        foreach($furniture_details as $item){
            $category = ItemCategory::where('name', $item['category'])->first();
            $item_referrence_terminology = ItemReferenceTerminology::where('code', $item['code'])->first();

            $furnitures[] = [
                'name' => $item_referrence_terminology['system'].'-'.$item_referrence_terminology['code'],
                'reference_terminology_id' => $item_referrence_terminology->id,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $office_supply_details = [
            ['category' => 'Office Supply', 'code' => 'Regular'],
            ['category' => 'Office Supply', 'code' => 'High-end'],
            ['category' => 'Office Supply', 'code' => 'Mid-range'],
        ];

        $office_supplies = [];

        foreach($office_supply_details as $item){
            $category = ItemCategory::where('name', $item['category'])->first();
            $item_referrence_terminology = ItemReferenceTerminology::where('code', $item['code'])->first();

            $office_supplies[] = [
                'name' => $item_referrence_terminology['system'].'-'.$item_referrence_terminology['code'],
                'reference_terminology_id' => $item_referrence_terminology->id,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $medical = [
            "category" => "Medical Supply",
            "snomed_codes" => [
                36806, 10834, 15064, 11218, 18567, 10475, 10260, 17357, 36625, 10323,
                11099, 10668, 17094, 35649, 14960, 34842, 46797, 10648, 36830, 36829,
                15993, 10705, 17359, 36834, 12108, 10398, 35140, 10996, 11096, 10826,
                47120, 14197, 12845, 14457, 35249, 11598, 35282, 11624, 35327, 47101,
                40026, 17537, 16335, 12843, 10729, 12693, 11762, 11843, 11853, 35439,
                30880, 12053, 12163, 40046, 12224, 37172, 12984, 13247, 16733, 11462,
                13264, 35201, 17145, 17377, 14421, 11090, 35003, 10072, 12759, 47183,
                40019, 47102, 40030, 17399, 14227, 14407, 10793, 14415, 40029, 10586,
                14389, 16910, 40700, 40755, 16730, 40023, 36814, 47121, 10722, 14217,
                15222, 34840, 15516, 36835, 10769, 14949, 16609, 17360, 17565, 36873,
                16361, 10404, 16388, 16345, 14959, 40017, 16828, 16834, 35431, 35914,
                16790, 40040, 16732, 40048, 35913, 37170, 12739, 37176, 15477, 17462,
                17540
            ]
        ];

        $snomed_cts = [];

        foreach($medical['snomed_codes'] as $item){
            $category = ItemCategory::where('name', $medical['category'])->first();
            $item_referrence_terminology = ItemReferenceTerminology::where('code', $item)->first();

            $snomed_cts[] = [
                'name' => $item_referrence_terminology['system'].'-'.$item,
                'reference_terminology_id' => $item_referrence_terminology->id,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $loinc_codes = [
            "category" => "Laboratory Supply",
            "codes" => [
                '17853-2', '1558-6', '2075-0', '2093-3', '1989-3', '883-9', '24336-0', '2143-6', '3184-9', '1834-1',
                '1742-6', '1751-7', '2882-9', '6768-6', '19359-6', '17861-6', '16927-2', '22322-2', '38576-5', '30239-8',
                '32018-8', '13950-1', '13949-3', '22318-0', '13955-0', '7918-6', '22420-4', '22418-8', '3024-7', '3056-9',
                '24113-3', '5316-4', '5341-2', '1920-8', '14632-7', '21198-7', '3094-0', '3084-1', '30948-1', '11047-6',
                '2339-0', '1988-5', '22688-8', '2271-4', '2276-0', '57021-8', '8126-1', '20395-0', '2157-6', '2158-4',
                '21571-8', '21572-6', '1987-7', '2143-0', '2160-0', '48065-7', '2233-3', '2085-9', '18262-6', '32533-7',
                '32532-9', '3030-2', '4537-7', '23310-4', '2276-4', '14979-1', '15074-8', '2857-1', '3036-8', '2324-2',
                '2345-7', '1559-0', '56009-4', '19186-2', '4548-4', '5199-2', '51984-0', '32286-7', '51985-7', '718-7',
                '5197-9', '5198-7', '54086-4', '6598-7', '64164-1', '1752-2', '12839-5', '15301-5', '6228-3', '2986-8',
                '29594-8', '2498-4', '14804-9', '1744-7', '2951-2', '2041-4', '26456-1', '24320-4', '58410-2', '30341-2',
                '58045-4'
            ]
        ];

        $loinc = [];

        foreach($loinc_codes['codes'] as $item){
            $category = ItemCategory::where('name', $loinc_codes['category'])->first();
            $item_referrence_terminology = ItemReferenceTerminology::where('code', $item)->first();

            $loinc[] = [
                'name' => $item_referrence_terminology['system'].'-'.$item,
                'reference_terminology_id' => $item_referrence_terminology->id,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $gmdn_codes = [
            'category' => 'Laboratory Supply',
            'codes' => [
                45792, 57839, 58034, 36223, 36224, 58064, 58065, 62020, 98765, 46413, 36248, 35138, 35385, 58193,
                37277, 58284, 45212, 35882, 35883, 46874, 35988, 47998, 59288, 46983, 46114
            ]
        ];

        $gmdn = [];

        foreach($gmdn_codes['codes'] as $item){
            $category = ItemCategory::where('name', $gmdn_codes['category'])->first();
            $item_referrence_terminology = ItemReferenceTerminology::where('code', $item)->first();

            $gmdn[] = [
                'name' => $item_referrence_terminology['system'].'-'.$item,
                'reference_terminology_id' => $item_referrence_terminology->id,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $terminologies_pivot = array_merge($ict, $furnitures, $office_supplies, $snomed_cts, $loinc, $gmdn);

        TerminologyCategory::insert($terminologies_pivot);
    }
}
