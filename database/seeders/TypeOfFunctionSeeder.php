<?php

namespace Database\Seeders;

use App\Models\TypeOfFunction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeOfFunctionSeeder extends Seeder
{
    /**
     * Generate a random 4-digit number
     *
     * @return string
     */
    private function generateRandom4Digits()
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'TYPE-STRAT-' . $this->generateRandom4Digits(),
                "type" => 'strategic',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                "code" => "TYPE-CORE-" . $this->generateRandom4Digits(),
                "type" => 'core',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                "code" => "TYPE-SUPP-" . $this->generateRandom4Digits(),
                "type" => 'support',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        TypeOfFunction::insert($types);
    }
}
