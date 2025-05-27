<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\ItemClassification;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitIds = ItemUnit::pluck('id')->toArray();
        $categoryIds = ItemCategory::pluck('id')->toArray();

        // Map classification codes to IDs
        $classifications = ItemClassification::pluck('id', 'code')->toArray();

        $items = [
            ['name' => 'A4 Bond Paper', 'code' => 'ITM-PAP-001', 'estimated_budget' => 250.00, 'classification_code' => 'SUP-OFF'],
            ['name' => 'Ballpoint Pen', 'code' => 'ITM-PEN-001', 'estimated_budget' => 120.00, 'classification_code' => 'SUP-OFF'],
            ['name' => 'Stapler', 'code' => 'ITM-STP-001', 'estimated_budget' => 300.00, 'classification_code' => 'SUP-OFF'],
            ['name' => 'Paper Clips', 'code' => 'ITM-CLP-001', 'estimated_budget' => 50.00, 'classification_code' => 'SUP-OFF'],
            ['name' => 'Sticky Notes', 'code' => 'ITM-STK-001', 'estimated_budget' => 200.00, 'classification_code' => 'SUP-OFF'],

            ['name' => 'Laptop', 'code' => 'ITM-LAP-001', 'estimated_budget' => 45000.00, 'classification_code' => 'EQP-IT'],
            ['name' => 'Wireless Mouse', 'code' => 'ITM-MOU-001', 'estimated_budget' => 600.00, 'classification_code' => 'EQP-IT'],
            ['name' => 'Keyboard', 'code' => 'ITM-KBD-001', 'estimated_budget' => 2500.00, 'classification_code' => 'EQP-IT'],
            ['name' => 'Monitor', 'code' => 'ITM-MON-001', 'estimated_budget' => 12000.00, 'classification_code' => 'EQP-IT'],
            ['name' => 'External Hard Drive', 'code' => 'ITM-HDD-001', 'estimated_budget' => 4500.00, 'classification_code' => 'EQP-IT'],

            ['name' => 'Office Chair', 'code' => 'ITM-CHR-001', 'estimated_budget' => 3500.00, 'classification_code' => 'EQP-MED'],
            ['name' => 'Office Desk', 'code' => 'ITM-DSK-001', 'estimated_budget' => 5500.00, 'classification_code' => 'EQP-MED'],
            ['name' => 'Filing Cabinet', 'code' => 'ITM-CAB-001', 'estimated_budget' => 4500.00, 'classification_code' => 'EQP-MED'],
            ['name' => 'Conference Table', 'code' => 'ITM-TBL-001', 'estimated_budget' => 12000.00, 'classification_code' => 'EQP-MED'],
            ['name' => 'Bookshelf', 'code' => 'ITM-BKS-001', 'estimated_budget' => 3000.00, 'classification_code' => 'EQP-MED'],
            ['name' => 'Whiteboard', 'code' => 'ITM-WB-001', 'estimated_budget' => 2000.00, 'classification_code' => 'EQP-MED'],

            ['name' => 'Projector', 'code' => 'ITM-PJT-001', 'estimated_budget' => 15000.00, 'classification_code' => 'TRN-CAP'],
            ['name' => 'First Aid Kit', 'code' => 'ITM-FAK-001', 'estimated_budget' => 800.00, 'classification_code' => 'SUP-MEDLAB'],
            ['name' => 'Fire Extinguisher', 'code' => 'ITM-FEX-001', 'estimated_budget' => 1500.00, 'classification_code' => 'REP-MAIN'],
            ['name' => 'Coffee Maker', 'code' => 'ITM-CFM-001', 'estimated_budget' => 2500.00, 'classification_code' => 'UTIL-COM'],
        ];

        foreach ($items as $item) {
            Item::create([
                'name' => $item['name'],
                'code' => $item['code'],
                'estimated_budget' => $item['estimated_budget'],
                'item_category_id' => $this->getRandomId($categoryIds),
                'item_unit_id' => $this->getRandomId($unitIds),
                'item_classification_id' => $classifications[$item['classification_code']] ?? null,
            ]);
        }
    }

    private function getRandomId(array $ids): int
    {
        return $ids[array_rand($ids)];
    }
}
