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

            // Furniture
            [
                'name' => 'Office Chair',
                'code' => 'ITM-CHR-001',
                'variant' => 'Ergonomic, Adjustable Height',
                'estimated_budget' => 3500.00,
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
