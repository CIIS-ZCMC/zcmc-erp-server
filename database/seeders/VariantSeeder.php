<?php

namespace Database\Seeders;

use App\Models\Variant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = [
            [
                'name' => 'Regular',
                'code' => 'regular'
            ],
            [
                'name' => 'Mid-Range',
                'code' => 'mid-range'
            ],
            [
                'name' => 'High-end',
                'code' => 'high-end'
            ]
        ];

        foreach($variants as $variant){
            Variant::create($variant);
        }
    }
}
