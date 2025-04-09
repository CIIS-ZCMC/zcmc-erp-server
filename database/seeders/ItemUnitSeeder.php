<?php

namespace Database\Seeders;

use App\Models\ItemUnit;
use Illuminate\Database\Seeder;

class ItemUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Piece',
                'code' => 'PC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pack',
                'code' => 'PK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Box',
                'code' => 'BX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bottle',
                'code' => 'BT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pax',
                'code' => 'PAX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Set',
                'code' => 'SET',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lot',
                'code' => 'LOT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unit',
                'code' => 'UNT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kilogram',
                'code' => 'KG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ream',
                'code' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Roll',
                'code' => 'RL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Can',
                'code' => 'CAN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gallon',
                'code' => 'GL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pair',
                'code' => 'PR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kit',
                'code' => 'KT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tin',
                'code' => 'TIN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tube',
                'code' => 'TUBE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meter',
                'code' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liter',
                'code' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pad',
                'code' => 'PAD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Drum',
                'code' => 'DR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bundle',
                'code' => 'BDL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test',
                'code' => 'TST',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vial',
                'code' => 'VL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tank',
                'code' => 'TK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cartridge',
                'code' => 'CRTG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sachet',
                'code' => 'SC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sheet',
                'code' => 'SH',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Plate',
                'code' => 'PL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rack',
                'code' => 'RK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tray',
                'code' => 'TR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Loaf',
                'code' => 'LOF',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Case',
                'code' => 'CS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pail',
                'code' => 'PAL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Card',
                'code' => 'CARD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Square Feet',
                'code' => 'FT2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Capsule',
                'code' => 'CAP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Solution',
                'code' => 'SOL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jar',
                'code' => 'JAR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sack',
                'code' => 'SK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bar',
                'code' => 'BR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inch per Minute',
                'code' => 'INM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Subscription',
                'code' => 'SUB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Account',
                'code' => 'ACC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Seat',
                'code' => 'ST',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        ItemUnit::insert($units);
    }
}
