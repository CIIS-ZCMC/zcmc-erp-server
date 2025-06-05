<?php

namespace Database\Seeders;

use App\Models\ItemReferenceTerminology;
use Illuminate\Database\Seeder;

class ItemReferenceTerminologiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $variants = [
            [ "code" => "Regular", "system" => "Variant", "description" => null, "created_at" => now(), "updated_at" => now()],
            [ "code" => "Mid-Range", "system" => "Variant", "description" => null, "created_at" => now(), "updated_at" => now()],
            [ "code" => "High-end", "system" => "Variant", "description" => null, "created_at" => now(), "updated_at" => now()],
            [ "code" => "Online", "system" => "Variant", "description" => null, "created_at" => now(), "updated_at" => now()],
            [ "code" => "Enterprise", "system" => "Variant", "description" => null, "created_at" => now(), "updated_at" => now()],
        ];

        $snomed_codes = [
            36806, 10834, 15064, 11218, 18567, 10475, 10260, 17357, 36625, 10323,
            11099, 10668, 17094, 35649, 14960, 34842, 46797, 10648, 36830, 36829,
            15993, 10705, 17359, 36834, 12108, 10398, 35140, 10996, 11096, 10826,
            47120, 14197, 12845, 14457, 35249, 11598, 35282, 11624, 35327, 47101,
            40026, 17537, 16335, 12843, 10729, 12693, 11762, 11843, 11853, 35439,
            30880, 12053, 12163, 40046, 12224, 37172, 12984, 13247, 16733, 11462,
            13264, 35201, 17145, 17377, 14421, 11090, 35003, 10072, 12759, 47183,
            40019, 47102, 40030, 17399, 14227, 14407, 10793, 14415, 40029, 10586,
            14389, 16910, 40700, 40755, 16730, 40023, 36814, 47121, 10722, 14217,
            15222, 34840, 15516, 36835, 10769, 14949, 16609, 17360, 17565, 36873,
            16361, 10404, 16388, 16345, 14959, 40017, 16828, 16834, 35431, 35914,
            16790, 40040, 16732, 40048, 35913, 37170, 12739, 37176, 15477, 17462,
            17540
        ];
        
        $snomed_ct = [];

        foreach($snomed_codes as $code){
            $snomed_ct[] = [
                "code" => $code,
                "system" => "Snomed CT",
                "description" => null,
                "created_at" => now(),
                "updated_at" => now()
            ];
        }

        $loinc_codes = [
            '17853-2', '1558-6', '2075-0', '2093-3', '1989-3', '883-9', '24336-0', '2143-6', '3184-9', '1834-1',
            '1742-6', '1751-7', '2882-9', '6768-6', '19359-6', '17861-6', '16927-2', '22322-2', '38576-5', '30239-8',
            '32018-8', '13950-1', '13949-3', '22318-0', '13955-0', '7918-6', '22420-4', '22418-8', '3024-7', '3056-9',
            '24113-3', '5316-4', '5341-2', '1920-8', '14632-7', '21198-7', '3094-0', '3084-1', '30948-1', '11047-6',
            '2339-0', '1988-5', '22688-8', '2271-4', '2276-0', '57021-8', '8126-1', '20395-0', '2157-6', '2158-4',
            '21571-8', '21572-6', '1987-7', '2143-0', '2160-0', '48065-7', '2233-3', '2085-9', '18262-6', '32533-7',
            '32532-9', '3030-2', '4537-7', '23310-4', '2276-4', '14979-1', '15074-8', '2857-1', '3036-8', '2324-2',
            '2345-7', '1559-0', '56009-4', '19186-2', '4548-4', '5199-2', '51984-0', '32286-7', '51985-7', '718-7',
            '5197-9', '5198-7', '54086-4', '6598-7', '64164-1', '1752-2', '12839-5', '15301-5', '6228-3', '2986-8',
            '29594-8', '2498-4', '14804-9', '1744-7', '2951-2', '2041-4', '26456-1', '24320-4', '58410-2', '30341-2',
            '58045-4'
        ];

        $loinc = [];

        foreach($loinc_codes as $code){
            $loinc[] = [
                'code' => $code,
                'system' => "LOINC",
                'description' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $gmdn_codes = [
            45792, 57839, 58034, 36223, 36224, 58064, 58065, 62020, 98765, 46413, 36248, 35138, 35385, 58193,
            37277, 58284, 45212, 35882, 35883, 46874, 35988, 47998, 59288, 46983, 46114
        ];

        $gmdn = [];

        foreach($gmdn_codes as $code){
            $gmdn[] = [
                'code' => $code,
                'system' => "GMDN",
                'description' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $pnf_eml_codes = [
            [
                'code' => 1,
                'system' => "PNF/EML",
                'description' => 'Anaesthetic Drugs: Includes general and local anesthetics, and adjuncts.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 2,
                'system' => "PNF/EML",
                'description' => 'Cardiovascular Drugs: Covers various heart and blood vessel medications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 3,
                'system' => "PNF/EML",
                'description' => 'Analgesics: Pain relievers.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 4,
                'system' => "PNF/EML",
                'description' => 'Anticonvulsants: For seizure disorders.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 5,
                'system' => "PNF/EML",
                'description' => 'Antihistamines: For allergies.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 6,
                'system' => "PNF/EML",
                'description' => 'Anti-infective Drugs: A broad category for antibiotics, antivirals, antifungals, anti-TB, etc.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 8,
                'system' => "PNF/EML",
                'description' => 'Antineoplastic Drugs: Cancer medications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 10,
                'system' => "PNF/EML",
                'description' => 'Dermatological Drugs: Skin medications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 11,
                'system' => "PNF/EML",
                'description' => 'Gastrointestinal Drugs: For digestive system conditions.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 13,
                'system' => "PNF/EML",
                'description' => 'Hypoglycemic Drugs: For diabetes.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 19,
                'system' => "PNF/EML",
                'description' => 'Immunologicals: Vaccines and sera.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 20,
                'system' => "PNF/EML",
                'description' => 'Oxytocics: For uterine contractions.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 21,
                'system' => "PNF/EML",
                'description' => 'Ophthalmic Drugs: Eye medications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 23,
                'system' => "PNF/EML",
                'description' => 'Neurological Drugs: For nervous system conditions.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 24,
                'system' => "PNF/EML",
                'description' => 'Psychotherapeutic Drugs: Mental health medications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 25,
                'system' => "PNF/EML",
                'description' => 'Antitoxic Agents: For poisonings and specific toxic effects.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 26,
                'system' => "PNF/EML",
                'description' => 'Respiratory Drugs: For lung conditions.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 27,
                'system' => "PNF/EML",
                'description' => 'Intravenous Solutions: IV fluids and electrolytes.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 28,
                'system' => "PNF/EML",
                'description' => 'Hormones: Endocrine medications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 29,
                'system' => "PNF/EML",
                'description' => 'Vitamins: Vitamin preparations.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => "N/A",
                'system' => "PNF/EML",
                'description' => ' For items that are not explicitly categorized as a drug (e.g., medical devices like cannulas, or where the PNF does not provide a specific numerical grouping for that exact item).',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        
        $terminologies = array_merge($variants, $snomed_ct, $loinc, $gmdn, $pnf_eml_codes);

        ItemReferenceTerminology::insert($terminologies);
    }
}
