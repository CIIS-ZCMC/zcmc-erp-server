<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemSpecification;

class ItemSpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample specifications that might be used across different items
        $specifications = [
            // Generic specifications
            [
                'description' => 'Must be brand new',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'Warranty of at least 1 year',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'Certified authentic',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'With after-sales support',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],

            // ICT Equipment specifications
            [
                'description' => 'Minimum 8GB RAM',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'SSD storage required',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],

            // Medical Equipment specifications
            [
                'description' => 'FDA approved',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'Must meet ISO 13485 standards',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],

            // Software specifications
            [
                'description' => 'Multi-user license',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'Must be compatible with Windows 10/11',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],

            // Furniture specifications
            [
                'description' => 'Ergonomic design',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
            [
                'description' => 'Fire-retardant materials',
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => null
            ],
        ];

        // Create the specifications
        foreach ($specifications as $spec) {
            ItemSpecification::create($spec);
        }

        // You can also create hierarchical specifications if needed
        $mainSpec = ItemSpecification::create([
            'description' => 'Computer specifications',
            'item_id' => null,
            'item_request_id' => null,
            'item_specification_id' => null
        ]);

        // Sub-specifications for computer
        $computerSpecs = [
            ['description' => 'Intel Core i5 or equivalent', 'item_specification_id' => $mainSpec->id],
            ['description' => 'Minimum 256GB SSD storage', 'item_specification_id' => $mainSpec->id],
            ['description' => '14-inch Full HD display', 'item_specification_id' => $mainSpec->id],
        ];

        foreach ($computerSpecs as $spec) {
            ItemSpecification::create([
                'description' => $spec['description'],
                'item_id' => null,
                'item_request_id' => null,
                'item_specification_id' => $spec['item_specification_id']
            ]);
        }
    }
}
