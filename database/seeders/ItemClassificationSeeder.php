<?php

namespace Database\Seeders;

use App\Models\ItemClassification;
use Illuminate\Database\Seeder;

class ItemClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            [
                'name' => 'Medical Equipment',
                'code' => 'EQP-MED',
                'description' => 'Capital outlay for acquisition or replacement of medical equipment.'
            ],
            [
                'name' => 'IT Equipment',
                'code' => 'EQP-IT',
                'description' => 'Hardware and devices used in hospital and office automation.'
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'SUP-OFF',
                'description' => 'Common-use supplies like paper, pens, folders, and ink.'
            ],
            [
                'name' => 'Drugs and Medicines',
                'code' => 'PHARMA',
                'description' => 'Essential and emergency drugs for hospital operations.'
            ],
            [
                'name' => 'Medical and Laboratory Supplies',
                'code' => 'SUP-MEDLAB',
                'description' => 'Consumables used in patient care and laboratory diagnostics.'
            ],
            [
                'name' => 'Training and Capacity Building',
                'code' => 'TRN-CAP',
                'description' => 'Expenses related to staff training, seminars, and workshops.'
            ],
            [
                'name' => 'Repair and Maintenance',
                'code' => 'REP-MAIN',
                'description' => 'Maintenance and servicing of equipment, vehicles, and facilities.'
            ],
            [
                'name' => 'Infrastructure Projects',
                'code' => 'INFRA',
                'description' => 'Construction, renovation, and physical improvement projects.'
            ],
            [
                'name' => 'Utilities and Communication',
                'code' => 'UTIL-COM',
                'description' => 'Electricity, water, internet, and telephone expenses.'
            ],
        ];

        foreach ($classifications as $classification) {
            ItemClassification::create($classification);
        }
    }
}
