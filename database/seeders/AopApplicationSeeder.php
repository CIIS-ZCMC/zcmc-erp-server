<?php

namespace Database\Seeders;

use App\Models\AssignedArea;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\ProcurementModes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\AopApplication;
use App\Models\ApplicationObjective;
use App\Models\Activity;
use App\Models\TypeOfFunction;
use App\Models\SuccessIndicator;
use App\Models\Objective;
use App\Models\Target;
use App\Models\Resource;
use App\Models\ResponsiblePerson;
use App\Models\OtherObjective;
use App\Models\OtherSuccessIndicator;
use App\Models\User;
use App\Models\Designation;
use App\Models\Division;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;
use Carbon\Carbon;

class AopApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This creates sample AOP Applications following the process flow
     */
    public function run(): void
    {
        // Get existing organizational structure data from the database
        // These have been imported from UMIS
        $divisions = Division::all();
        if ($divisions->isEmpty()) {
            $this->command->error('No divisions found. Please run the UMIS import command first.');
            return;
        }

        $departments = Department::all();
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please run the UMIS import command first.');
            return;
        }

        $sections = Section::all();
        if ($sections->isEmpty()) {
            $this->command->error('No sections found. Please run the UMIS import command first.');
            return;
        }

        $units = Unit::all();
        if ($units->isEmpty()) {
            $this->command->error('No units found. Please run the UMIS import command first.');
            return;
        }

        $designations = Designation::all();
        if ($designations->isEmpty()) {
            $this->command->error('No designations found. Please run the UMIS import command first.');
            return;
        }

        // Helper function to get random items from collections
        $getRandomItem = function ($collection) {
            return $collection->random(1)->first();
        };

        // Create sample users for organizational structure references with random assignments
        $divisionHead = $getRandomItem($designations);
        $randomDivision = $getRandomItem($divisions);

        // Create 5 AOP Applications with different data
        $missionStatements = [
            'To provide excellent healthcare services to the community through strategic planning and resource management',
            'To deliver compassionate and high-quality healthcare to all patients while fostering innovation and education',
            'To improve community health through accessible, comprehensive, and patient-centered care',
            'To be the leading healthcare provider committed to excellence, innovation, and patient satisfaction',
            'To enhance public health through preventive care, education, and advanced medical services'
        ];

        $statusOptions = ['draft', 'submitted', 'approved', 'rejected', 'in_review'];
        $remarkOptions = [
            'Initial AOP application draft',
            'Submitted for review by division chief',
            'Approved by all stakeholders',
            'Rejected due to budget constraints',
            'Currently under review by planning officer'
        ];

        $aopApplications = [];

        // Get first user as default owner and find users with specific designations
        // Find users with various designations or create dummy users if none exist
        $user = User::first() ?? User::factory()->create();

        $divisionChief = Division::where('name', 'Hospital Operations & Patient Support Service')->first();
        $mccChief = Division::where('name', 'Office of Medical Center Chief')->first();
        $planningOfficer = Section::where('name', 'IISU')->first();

        // Create 5 sample AOP Applications with corresponding PPMP Applications
        for ($i = 0; $i < 5; $i++) {
            // Get random user for each application
            $randomUser = User::inRandomOrder()->first() ?? $user;
            $sector = $randomUser->assignedArea->findDetails();

            $aopApplication = AopApplication::create([
                'user_id' => $randomUser->id,
                'division_chief_id' => $divisionChief->head_id,
                'mcc_chief_id' => $mccChief->head_id,
                'planning_officer_id' => $planningOfficer->head_id ?? 494,
                'mission' => $missionStatements[$i] ?? 'Default mission statement',
                'status' => $statusOptions[array_rand($statusOptions)],
                'has_discussed' => (bool) rand(0, 1),
                'remarks' => $remarkOptions[array_rand($remarkOptions)],
                'sector' => $sector['sector'],
                'sector_id' => $sector['details']['id']
            ]);

            $aopApplications[] = $aopApplication;
        }

        // Get or create Type of Functions (strategic, core, support)
        $typeOfFunctions = TypeOfFunction::all();
        if ($typeOfFunctions->isEmpty()) {
            $typeOfFunctions = collect(['strategic', 'core', 'support'])->map(function ($type) {
                return TypeOfFunction::create(['type' => $type]);
            });
        }

        // Create function objectives if they don't exist
        $strategicObjectives = [
            'Improve healthcare quality and patient satisfaction',
            'Enhance medical facility infrastructure',
            'Develop workforce capabilities'
        ];

        $coreObjectives = [
            'Provide accessible and affordable healthcare services',
            'Implement innovative medical treatments',
            'Strengthen community health programs'
        ];

        $supportObjectives = [
            'Optimize resource allocation and utilization',
            'Enhance information management systems',
            'Improve organizational processes and procedures'
        ];

        // Map objectives to function types
        $objectivesByType = [
            'strategic' => $strategicObjectives,
            'core' => $coreObjectives,
            'support' => $supportObjectives
        ];

        // Create objectives for each type of function
        $createdObjectives = [];
        foreach ($typeOfFunctions as $typeOfFunction) {
            $objectives = $objectivesByType[$typeOfFunction->type] ?? [];

            foreach ($objectives as $objectiveDescription) {
                $objectiveCode = 'OBJ-' . strtoupper(substr(str_replace(' ', '', $objectiveDescription), 0, 5)) . '-' . rand(100, 999);

                $objective = Objective::firstOrCreate(
                    ['code' => $objectiveCode],
                    [
                        'type_of_function_id' => $typeOfFunction->id,
                        'description' => $objectiveDescription
                    ]
                );

                $createdObjectives[$typeOfFunction->type][] = $objective;
            }
        }

        // Create sample success indicators linked to objectives
        $successIndicators = [
            'SI-01' => 'Increased patient satisfaction rate by 20%',
            'SI-02' => 'Reduced waiting time by 30%',
            'SI-03' => 'Improved staff efficiency by 15%',
            'SI-04' => 'Decreased operational costs by 10%',
            'SI-05' => 'Increased service coverage by 25%'
        ];

        // Create success indicators for each objective
        $allObjectives = Objective::all();
        $createdSuccessIndicators = [];

        foreach ($allObjectives as $objective) {
            // Create at least one success indicator per objective
            $randomCode = array_rand($successIndicators);

            $successIndicator = SuccessIndicator::firstOrCreate(
                ['code' => $randomCode, 'objective_id' => $objective->id],
                ['description' => $successIndicators[$randomCode]]
            );

            $createdSuccessIndicators[] = $successIndicator;
        }

        // For each AOP application, create application objectives linking to existing objectives
        foreach ($aopApplications as $appIndex => $aopApplication) {
            // For each type of function, create application objectives
            foreach ($typeOfFunctions as $typeOfFunction) {
                // Get available objectives for this function type
                $availableObjectives = $createdObjectives[$typeOfFunction->type] ?? [];

                // Skip if no objectives available for this type
                if (empty($availableObjectives)) {
                    continue;
                }

                // Select 1-2 objectives for this application based on app index for variety
                $objectiveCount = min(count($availableObjectives), 2);
                $startIndex = $appIndex % count($availableObjectives);

                for ($i = 0; $i < $objectiveCount; $i++) {
                    $index = ($startIndex + $i) % count($availableObjectives);
                    $objective = $availableObjectives[$index];

                    // Find success indicators for this objective
                    $successIndicators = SuccessIndicator::where('objective_id', $objective->id)->get();

                    // If no success indicators exist for this objective, create one
                    if ($successIndicators->isEmpty()) {
                        $randomCode = 'SI-' . rand(10, 99);
                        $successIndicator = SuccessIndicator::create([
                            'objective_id' => $objective->id,
                            'code' => $randomCode,
                            'description' => 'Success indicator for ' . $objective->description
                        ]);
                    } else {
                        $successIndicator = $successIndicators->first();
                    }

                    // Create application objective
                    $applicationObjective = ApplicationObjective::create([
                        'aop_application_id' => $aopApplication->id,
                        'objective_id' => $objective->id,
                        'success_indicator_id' => $successIndicator->id
                    ]);

                    // // For certain application objectives, create both OtherObjective and OtherSuccessIndicator
                    // // to ensure consistency in the "others" custom entries
                    // if ($index == 0 && $appIndex % 2 == 0) { // Only for even-indexed applications
                    //     // Create custom objective
                    //     OtherObjective::create([
                    //         'application_objective_id' => $applicationObjective->id,
                    //         'description' => 'Custom objective description for ' . $typeOfFunction->type . ' (Application ' . ($appIndex + 1) . ')'
                    //     ]);

                    //     // Also create matching custom success indicator
                    //     OtherSuccessIndicator::create([
                    //         'application_objective_id' => $applicationObjective->id,
                    //         'description' => 'Custom success indicator for ' . $typeOfFunction->type . ' (Application ' . ($appIndex + 1) . ')'
                    //     ]);
                    // }

                    // Create activities for this objective
                    $this->createActivities($applicationObjective, $user);
                }
            }
        }
    }

    /**
     * Create activities for an application objective
     */
    private function createActivities($applicationObjective, $user)
    {
        // Get all collections for random assignments
        $divisions = Division::all();
        $departments = Department::all();
        $sections = Section::all();
        $units = Unit::all();
        $designations = Designation::all();

        // Helper function to get random items from collections
        $getRandomItem = function ($collection) {
            return $collection->random(1)->first();
        };

        $activityNames = [
            'Training and development program',
            'Equipment procurement',
            'Community outreach initiative',
            'Process improvement project',
            'Staff engagement activities',
            'Quality improvement initiative',
            'Research and development project',
            'Patient care enhancement program',
            'Technology implementation',
            'Facility maintenance and upgrade'
        ];

        $expenseClasses = ['MOOE', 'CO', 'PS'];

        // Create 1-2 activities per objective (reduced from 1-3 to avoid creating too many records)
        $activityCount = rand(1, 2);
        for ($i = 0; $i < $activityCount; $i++) {
            $activityName = $activityNames[array_rand($activityNames)];
            $startMonth = Carbon::now()->startOfYear()->addMonths(rand(0, 6));
            $endMonth = (clone $startMonth)->addMonths(rand(1, 5));

            // Input Activity details
            $activity = Activity::create([
                'application_objective_id' => $applicationObjective->id,
                'activity_code' => $applicationObjective->objective_code . '-ACT-' . ($i + 1),
                'name' => $activityName . ' ' . ($i + 1),
                'is_gad_related' => (bool) rand(0, 1),
                'cost' => rand(10000, 100000) / 100,
                'start_month' => $startMonth,
                'end_month' => $endMonth,
                'expense_class' => $expenseClasses[array_rand($expenseClasses)], //added by kim
            ]);

            // Create target by Quarter (including unit of target)
            $target = Target::create([
                'activity_id' => $activity->id,
                'first_quarter' => (string) rand(1, 10),
                'second_quarter' => (string) rand(1, 10),
                'third_quarter' => (string) rand(1, 10),
                'fourth_quarter' => (string) rand(1, 10)
                // Note: The migration doesn't have unit and quantity fields
            ]);

            // Create or use sample Item
            $item = DB::table('items')->first();
            if (!$item) {
                // Create necessary prerequisites for item
                $classificationId = DB::table('item_classifications')->insertGetId([
                    'name' => 'Sample Classification',
                    'code' => 'CLASS-' . rand(100, 999),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $categoryId = DB::table('item_categories')->first()->id ??
                    DB::table('item_categories')->insertGetId([
                        'name' => 'Sample Category',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                $unitId = DB::table('item_units')->insertGetId([
                    'name' => 'piece',
                    'code' => 'pc',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $itemId = DB::table('items')->insertGetId([
                    'item_classification_id' => $classificationId,
                    'item_category_id' => $categoryId,
                    'item_unit_id' => $unitId,
                    'name' => 'Sample Item',
                    'estimated_budget' => $activity->cost,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $itemId = $item->id;
            }

            // Create or use sample Purchase Type
            $purchaseType = DB::table('purchase_types')->first();

            if (!$purchaseType) {
                $purchaseTypeId = DB::table('purchase_types')->insertGetId([
                    'description' => 'Sample Purchase Type',
                    'code' => 'PURCHASE-01',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $purchaseTypeId = $purchaseType->id;
            }

            // Set expense class and object category
            $expenseClass = $expenseClasses[array_rand($expenseClasses)];
            $objectCategories = ['Equipment', 'Supplies', 'Services', 'Infrastructure'];

            // Create resource with correct fields
            Resource::create([
                'activity_id' => $activity->id,
                'item_id' => $itemId,
                'purchase_type_id' => $purchaseTypeId,
                'expense_class' => $expenseClass,
                // 'object_category' => $objectCategories[array_rand($objectCategories)],
                'quantity' => rand(1, 20)
            ]);

            // Get random organizational structure IDs
            $randomDivision = $getRandomItem($divisions);
            $randomDepartment = $departments->where('division_id', $randomDivision->id)->isNotEmpty()
                ? $getRandomItem($departments->where('division_id', $randomDivision->id))
                : $getRandomItem($departments);
            $randomSection = $sections->where('department_id', $randomDepartment->id)->isNotEmpty()
                ? $getRandomItem($sections->where('department_id', $randomDepartment->id))
                : $getRandomItem($sections);
            $randomUnit = $units->where('section_id', $randomSection->id)->isNotEmpty()
                ? $getRandomItem($units->where('section_id', $randomSection->id))
                : $getRandomItem($units);
            $randomDesignation = $getRandomItem($designations);

            // Create responsible person (person in-charge)
            ResponsiblePerson::create([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'division_id' => $randomDivision->id,
                'department_id' => $randomDepartment->id,
                'section_id' => $randomSection->id,
                'unit_id' => $randomUnit->id,
                'designation_id' => $randomDesignation->id
            ]);
        }
    }
}
