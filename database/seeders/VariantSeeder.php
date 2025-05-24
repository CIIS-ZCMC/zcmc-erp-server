<?php

namespace Database\Seeders;

use App\Models\Variant;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = [
            ['name' => '70gsm, 500 sheets'],
            ['name' => 'Black Ink, Box of 12'],
            ['name' => 'Medium Size, 20 Sheets Capacity'],
            ['name' => '33mm, Box of 100'],
            ['name' => '3x3 Inches, Pack of 5'],
            ['name' => '15.6" FHD, 8GB RAM, 256GB SSD'],
            ['name' => 'Optical, USB Receiver'],
            ['name' => 'Mechanical, RGB Backlit'],
            ['name' => '24" FHD, IPS Panel'],
            ['name' => '1TB, USB 3.0'],
            ['name' => 'Ergonomic, Adjustable Height'],
            ['name' => '120x60 cm, Wooden Finish'],
            ['name' => '4 Drawers, Metal'],
            ['name' => '8-Seater, Oval Shape'],
            ['name' => '5 Shelves, Wooden'],
            ['name' => '4x3 Feet, Magnetic'],
            ['name' => '3000 Lumens, HDMI'],
            ['name' => 'Complete Set'],
            ['name' => '2kg, ABC Type'],
            ['name' => '12-Cup Programmable'],
        ];

        $prefixes = [
            'paper' => 'PAP',
            'pen' => 'PEN',
            'stapler' => 'STP',
            'clip' => 'CLP',
            'sticky note' => 'STK',
            'laptop' => 'LAP',
            'mouse' => 'MOU',
            'keyboard' => 'KBD',
            'monitor' => 'MON',
            'hdd' => 'HDD',
            'chair' => 'CHR',
            'desk' => 'DSK',
            'cabinet' => 'CAB',
            'table' => 'TBL',
            'bookshelf' => 'BKS',
            'whiteboard' => 'WB',
            'projector' => 'PJT',
            'first aid kit' => 'FAK',
            'fire extinguisher' => 'FEX',
            'coffee maker' => 'CFM',
        ];

        $category_keywords = [
            'paper' => ['gsm', 'sheets'],
            'pen' => ['ink', 'box of 12'],
            'stapler' => ['sheets capacity'],
            'clip' => ['33mm'],
            'sticky note' => ['3x3'],
            'laptop' => ['ram', 'ssd'],
            'mouse' => ['usb receiver'],
            'keyboard' => ['keyboard', 'backlit'],
            'monitor' => ['monitor', 'ips'],
            'hdd' => ['1tb'],
            'chair' => ['adjustable height'],
            'desk' => ['wooden finish'],
            'cabinet' => ['drawers'],
            'table' => ['seater'],
            'bookshelf' => ['shelves'],
            'whiteboard' => ['magnetic'],
            'projector' => ['lumens'],
            'first aid kit' => ['complete set'],
            'fire extinguisher' => ['abc type'],
            'coffee maker' => ['programmable'],
        ];

        $counters = [];

        foreach ($variants as &$variant) {
            $name = strtolower($variant['name']);
            $category = 'misc';

            foreach ($category_keywords as $key => $keywords) {
                foreach ($keywords as $word) {
                    if (str_contains($name, strtolower($word))) {
                        $category = $key;
                        break 2;
                    }
                }
            }

            $prefix = $prefixes[$category] ?? 'GEN';
            $counters[$prefix] = ($counters[$prefix] ?? 0) + 1;

            $variant['code'] = sprintf('%s-V%02d', $prefix, $counters[$prefix]);

            Variant::create($variant);
        }
    }
}
