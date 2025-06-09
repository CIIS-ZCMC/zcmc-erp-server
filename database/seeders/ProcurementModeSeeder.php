<?php

namespace Database\Seeders;

use App\Models\ProcurementModes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcurementModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Competitive Bidding'],
            ['name' => 'Limited Source Bidding'],
            ['name' => 'Competitive Dialogue'],
            ['name' => 'Unsolicited Offer with Bid Matching'],
            ['name' => 'Direct Contracting'],
            ['name' => 'Direct Acquisition'],
            ['name' => 'Repeat Order'],
            ['name' => 'Small Value Procurement'],
            ['name' => 'Negotiated Procurement'],
            ['name' => 'Direct Sales'],
            ['name' => 'Direct Procurement for Science, Technology, and Innovation'],
        ];

        foreach ($data as $item) {
            ProcurementModes::create($item);
        }
    }
}
