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
        $unitNames = [
            'Ampule', 'Account', 'Bag', 'Bar', 'Bot', 'Bottle', 'Box', 'Bundle', 'Can', 'Capsule', 'Card',
            'Cart', 'Cartridge', 'Case', 'Drum', 'Gallon', 'Jar', 'Kg', 'Kit', 'Liter', 'Loaf',
            'Lot', 'Meter', 'Month', 'Nebule', 'Pack', 'Pad', 'Pail', 'Pair', 'Pax', 'Piece',
            'Plate', 'Rack', 'Ream', 'Roll', 'Sachet', 'Sack', 'Seat', 'Set', 'Sheet', 'Spool',
            'Tablet', 'Tank', 'Test', 'Tin', 'Tray', 'Tube', 'Unit', 'Vial'
        ];

        $units = [];

        foreach ($unitNames as $name) {
            $units[] = [
                'name' => $name,
                'code' => strtoupper(substr($name, 0, 3)), // Generates code like 'AMP', 'BAG', etc.
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ItemUnit::insert($units);
    }
}
