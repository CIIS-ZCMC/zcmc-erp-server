<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TerminologyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ict = [
            ['category' => 'ICT Equipment', 'type' => 'Regular'],
            ['category' => 'ICT Equipment', 'type' => 'Mid-range'],
            ['category' => 'ICT Equipment', 'type' => 'High-End'],
            ['category' => 'Software and Subscription', 'type' => 'Online'],
            ['category' => 'Software and Subscription', 'type' => 'Regular'],
            ['category' => 'Software and Subscription', 'type' => 'Enterprise'],
        ];

        $medical = [
            "category_name" => "Medical Supply",
            "snomed" => [
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
    }
}
