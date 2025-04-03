<?php

namespace Database\Seeders;

use App\Models\TypeOfFunction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeOfFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ["strategic", 'core', 'support'];

        foreach ($types as $type) {
            TypeOfFunction::create(['type' => $type]);
        }
    }
}
