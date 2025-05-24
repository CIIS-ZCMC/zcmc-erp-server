<?php

namespace Database\Seeders;

use App\Models\ItemUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ItemUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            'Account',
            'Ampule',
            'Bag',
            'Bar',
            'Bot',
            'Bottle',
            'Box',
            'Bundle',
            'Can',
            'Capsule',
            'Card',
            'Cart',
            'Cartridge',
            'Case',
            'Drum',
            'Gallon',
            'Jar',
            'Kg',
            'Kit',
            'Liter',
            'Loaf',
            'Lot',
            'Meter',
            'Month',
            'Nebule',
            'Pack',
            'Pad',
            'Pail',
            'Pair',
            'Pax',
            'Piece',
            'Plate',
            'Rack',
            'Ream',
            'Roll',
            'Sachet',
            'Sack',
            'Seat',
            'Set',
            'Sheet',
            'Spool',
            'Tablet',
            'Tank',
            'Test',
            'Tin',
            'Tray',
            'Tube',
            'Unit',
            'Vial'
        ];

        foreach ($units as $unit) {
            DB::table('item_units')->insertOrIgnore([
                'name' => $unit,
                'code' => strtoupper(str_replace([' ', '-', '.'], '_', $unit)),
                'description' => $unit,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
