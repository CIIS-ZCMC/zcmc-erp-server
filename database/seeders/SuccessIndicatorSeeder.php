<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\SuccessIndicator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuccessIndicatorSeeder extends Seeder
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
        $IDENTIFIER = 'SI';

        //STRAT-OBJ-RANDOM
        $indicators = [
            // Objective 1
            [
                'code' => $IDENTIFIER.'-PHU-'. $this->generateRandom4Digits(),
                'description' => 'Percent functionality of PHU in hospitals',
                'objective_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 2
            [
                'code' => $IDENTIFIER.'-GVA-'. $this->generateRandom4Digits(),
                'description' => 'Green Viability Assessment (GVA) Tool Percentage Score',
                'objective_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-PDT-'. $this->generateRandom4Digits(),
                'description' => 'Percent of capital formation to achieve priority development targets',
                'objective_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-AHT-'. $this->generateRandom4Digits(),
                'description' => 'Accreditation of the hospital to ISO 9001:2015',
                'objective_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-PGS-'. $this->generateRandom4Digits(),
                'description' => 'Accreditation of the hospital to PGS',
                'objective_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 3
            [
                'code' => $IDENTIFIER.'-SCE-'. $this->generateRandom4Digits(),
                'description' => 'Percentage of functional designated specialty centers established',
                'objective_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-PPB-'. $this->generateRandom4Digits(),
                'description' => 'Percent of patients in basic accommodation with zero co-payment',
                'objective_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 4
            [
                'code' => $IDENTIFIER.'-PHA-'. $this->generateRandom4Digits(),
                'description' => 'Percent of hospital areas that regularly process paperless EMR',
                'objective_id' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 5
            [
                'code' => $IDENTIFIER.'-PER6-'. $this->generateRandom4Digits(),
                'description' => 'Level 3: Percent of ER Patients with <6 hours TAT',
                'objective_id' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-PER4-'. $this->generateRandom4Digits(),
                'description' => 'Level 1-2: Percent of ER Patients with <4 hours TAT',
                'objective_id' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-PPD6-'. $this->generateRandom4Digits(),
                'description' => 'Percent of patients with <6 hours Discharge Process TAT',
                'objective_id' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-PIL5-'. $this->generateRandom4Digits(),
                'description' => 'Percent of inpatient laboratory test with <5 hours Discharge Process TAT',
                'objective_id' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 6
            [
                'code' => $IDENTIFIER.'-HAIR-'. $this->generateRandom4Digits(),
                'description' => 'Hospital Acquired Infection Rate',
                'objective_id' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 7
            [
                'code' => $IDENTIFIER.'-CESS-'. $this->generateRandom4Digits(),
                'description' => 'Client Experience Survey Score',
                'objective_id' => 7,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 8
            [
                'code' => $IDENTIFIER.'-DRCA-'. $this->generateRandom4Digits(),
                'description' => 'Disbursement rate of cash allocation',
                'objective_id' => 8,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 9
            [
                'code' => $IDENTIFIER.'-BURA-'. $this->generateRandom4Digits(),
                'description' => 'BUR: (a) Obligation Utilization Rate [95%, cumulative: quarterly target setting shall be at the discretion of the offices)]',
                'objective_id' => 9,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-BURB-'. $this->generateRandom4Digits(),
                'description' => 'BUR: (b) Disbursement Utilization Rate [85%, cumulative (quarterly target setting shall be at the discretion of the offices)]',
                'objective_id' => 9,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-BCA-'. $this->generateRandom4Digits(),
                'description' => 'BUR-CONAP 2024: (a) Obligation Utilization Rate [100%, cumulative (quarterly target setting shall be at the discretion of the offices)]',
                'objective_id' => 9,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-BCB-'. $this->generateRandom4Digits(),
                'description' => 'BUR-CONAP 2024: (b) Disbursement Utilization Rate [90%, cumulative (quarterly target setting shall be at the discretion of the offices)]',
                'objective_id' => 9,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 10
            [
                'code' => $IDENTIFIER.'-PIS-'. $this->generateRandom4Digits(),
                'description' => 'Percent of internal staff provided with learning and development interventions [100%, cumulative (quarterly target setting shall be at the discretion of the offices)]',
                'objective_id' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 11
            [
                'code' => $IDENTIFIER.'-POCA-'. $this->generateRandom4Digits(),
                'description' => 'Percent of other cross-cutting requirements complied within the prescribed timeline (a) Percent of nonconformities (or similar) responded with Request for Action within the prescribed timeline as incurred during the annual IQA and EQA (if applicable) [100%, demand-driven or as need arises (quarterly target shall be set at 100%)]',
                'objective_id' => 11,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-POCB-'. $this->generateRandom4Digits(),
                'description' => 'Percent of other cross-cutting requirements complied within the prescribed timeline (b) Percent of concerns closed [100%, demand-driven or as need arises (quarterly target shall be set at 100%)]',
                'objective_id' => 11,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-POCC-'. $this->generateRandom4Digits(),
                'description' => 'Percent of other cross-cutting requirements complied within the prescribed timeline (c) Percent of COA Audit Recommendations fully implemented (80%, demand-driven or as need arises (shall only be reported once the COA released the AOM, usually Q3 or Q4))',
                'objective_id' => 11,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-POCD-'. $this->generateRandom4Digits(),
                'description' => 'Percent of other cross-cutting requirements complied within the prescribed timeline (d) Percent of received FOI requests that were responded to within the prescribed timeline (100%, demand-driven or as need arises (quarterly target shall be set at 100%))',
                'objective_id' => 11,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => $IDENTIFIER.'-POCE-'. $this->generateRandom4Digits(),
                'description' => 'Percent of other cross-cutting requirements complied within the prescribed timeline (e) Percent of all documents/ requests processed within the prescribed timeline of office services in compliance with the Citizen\'s Charter (100%, demand-driven or as need arises (quarterly target shall be set at 100%))',
                'objective_id' => 11,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 12
            [
                'code' => $IDENTIFIER.'-PVP-'. $this->generateRandom4Digits(),
                'description' => 'Percent of vacant positions filled (disaggregated by type of position for DOH hospitals and DATRCs) within prescribed timelines with no invalidated appointment',
                'objective_id' => 12,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Objective 14
            [
                'code' => $IDENTIFIER.'-O-'. $this->generateRandom4Digits(),
                'description' => 'Others, please insert note/remarks',
                'objective_id' => 14,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];


        SuccessIndicator::insert($indicators);
    }
}
