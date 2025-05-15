<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemClassification;
use App\Models\ItemUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get IDs for relationships
        $unitIds = ItemUnit::pluck('id')->toArray();
        $categoryIds = ItemCategory::pluck('id')->toArray();

        // Sample items data
        $items = [
            // Office Supplies
            [
                'name' => 'A4 Bond Paper',
                'code' => 'ITM-PAP-001',
                'variant' => '70gsm, 500 sheets',
                'estimated_budget' => 250.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Ballpoint Pen',
                'code' => 'ITM-PEN-001',
                'variant' => 'Black Ink, Box of 12',
                'estimated_budget' => 120.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Stapler',
                'code' => 'ITM-STP-001',
                'variant' => 'Medium Size, 20 Sheets Capacity',
                'estimated_budget' => 300.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Paper Clips',
                'code' => 'ITM-CLP-001',
                'variant' => '33mm, Box of 100',
                'estimated_budget' => 50.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Sticky Notes',
                'code' => 'ITM-STK-001',
                'variant' => '3x3 Inches, Pack of 5',
                'estimated_budget' => 200.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],

            // IT Equipment
            [
                'name' => 'Laptop',
                'code' => 'ITM-LAP-001',
                'variant' => '15.6" FHD, 8GB RAM, 256GB SSD',
                'estimated_budget' => 45000.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Wireless Mouse',
                'code' => 'ITM-MOU-001',
                'variant' => 'Optical, USB Receiver',
                'estimated_budget' => 600.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Keyboard',
                'code' => 'ITM-KBD-001',
                'variant' => 'Mechanical, RGB Backlit',
                'estimated_budget' => 2500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Monitor',
                'code' => 'ITM-MON-001',
                'variant' => '24" FHD, IPS Panel',
                'estimated_budget' => 12000.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'External Hard Drive',
                'code' => 'ITM-HDD-001',
                'variant' => '1TB, USB 3.0',
                'estimated_budget' => 4500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],

            // Furniture
            [
                'name' => 'Office Chair',
                'code' => 'ITM-CHR-001',
                'variant' => 'Ergonomic, Adjustable Height',
                'estimated_budget' => 3500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Office Desk',
                'code' => 'ITM-DSK-001',
                'variant' => '120x60 cm, Wooden Finish',
                'estimated_budget' => 5500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Filing Cabinet',
                'code' => 'ITM-CAB-001',
                'variant' => '4 Drawers, Metal',
                'estimated_budget' => 4500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Conference Table',
                'code' => 'ITM-TBL-001',
                'variant' => '8-Seater, Oval Shape',
                'estimated_budget' => 12000.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Bookshelf',
                'code' => 'ITM-BKS-001',
                'variant' => '5 Shelves, Wooden',
                'estimated_budget' => 3000.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],

            // Miscellaneous
            [
                'name' => 'Whiteboard',
                'code' => 'ITM-WB-001',
                'variant' => '4x3 Feet, Magnetic',
                'estimated_budget' => 2000.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Projector',
                'code' => 'ITM-PJT-001',
                'variant' => '3000 Lumens, HDMI',
                'estimated_budget' => 15000.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'First Aid Kit',
                'code' => 'ITM-FAK-001',
                'variant' => 'Complete Set',
                'estimated_budget' => 800.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Fire Extinguisher',
                'code' => 'ITM-FEX-001',
                'variant' => '2kg, ABC Type',
                'estimated_budget' => 1500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
            [
                'name' => 'Coffee Maker',
                'code' => 'ITM-CFM-001',
                'variant' => '12-Cup Programmable',
                'estimated_budget' => 2500.00,
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }

        // Generate additional random items
        // Item::factory()->count(20)->create();
    }

    /**
     * Get a random ID from an array of IDs
     */
    private function getRandomId(array $ids): int
    {
        return $ids[array_rand($ids)];
    }
}
