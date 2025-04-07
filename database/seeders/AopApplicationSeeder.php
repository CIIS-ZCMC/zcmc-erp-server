<?php

namespace Database\Seeders;

use App\Models\AssignedArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\AopApplication;
use App\Models\ApplicationObjective;
use App\Models\Activity;
use App\Models\TypeOfFunction;
use App\Models\FunctionObjective;
use App\Models\SuccessIndicator;
use App\Models\Target;
use App\Models\Resource;
use App\Models\ResponsiblePerson;
use App\Models\OthersObjective;
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
        // Create sample users first for organizational structure references
        $sampleUser1 = User::factory()->create([
            'name' => 'Division Head',
            'email' => 'division.head@example.com',
            'umis_employee_profile_id' => 'EMP' . rand(10000, 99999),
        ]);
        
        $sampleUser2 = User::factory()->create([
            'name' => 'Department Head',
            'email' => 'department.head@example.com',
            'umis_employee_profile_id' => 'EMP' . rand(10000, 99999),
        ]);
        
        // Create or get organizational structure data
        $designation = Designation::first() ?? Designation::create([
            'name' => 'Staff Nurse',
            'code' => 'SN',
        ]);
        
        $division = Division::first() ?? Division::create([
            'head_id' => $sampleUser1->id,
            'oic_id' => null,
            'umis_division_id' => rand(1000, 9999),
            'name' => 'Medical Division',
        ]);
        
        $department = Department::first() ?? Department::create([
            'head_id' => $sampleUser2->id,
            'oic_id' => null,
            'division_id' => $division->id,
            'umis_department_id' => rand(1000, 9999),
            'name' => 'Nursing Department',
        ]);
        
        $section = Section::first() ?? Section::create([
            'head_id' => null,
            'oic_id' => null,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'umis_section_id' => rand(1000, 9999),
            'name' => 'Emergency Room',
        ]);
        
        $unit = Unit::first() ?? Unit::create([
            'head_id' => null,
            'oic_id' => null,
            'division_id' => $division->id,
            'section_id' => $section->id,
            'umis_unit_id' => rand(1000, 9999),
            'name' => 'Triage Unit',
        ]);
        
        // Find users for various roles
        $user = User::first() ?? User::factory()->create([
            'umis_employee_profile_id' => 'EMP' . rand(10000, 99999),
            'name' => 'Sample User',
            'email' => 'user@example.com',
            'profile_url' => 'https://randomuser.me/api/portraits/men/' . rand(1, 99) . '.jpg',
            'is_active' => true
        ]);

        AssignedArea::create([
            'user_id'=> $user->id,
            'designation_id' => $designation->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'section_id' => $section->id,
            'unit_id' => $unit->id,
        ]);
        
        // Create Division Chief Designation
        $divisionChiefDesignation = Designation::where('name', 'Division Chief')->first() ?? Designation::create([
            'name' => 'Division Chief',
            'code' => 'DC',
        ]);

        
        
        $divisionChief = User::where('id', '!=', $user->id)->first();

        if(!$divisionChief){
            $user = User::factory()->create([
                'umis_employee_profile_id' => 'EMP' . rand(10000, 99999),
                'name' => 'Division Chief',
                'email' => 'division.chief@example.com',
                'profile_url' => 'https://randomuser.me/api/portraits/men/' . rand(1, 99) . '.jpg',
                'is_active' => true
            ]);

            AssignedArea::create([
                'user_id'=> $user->id,
                'designation_id' => $divisionChiefDesignation->id,
                'division_id' => $division->id,
                'department_id' => null,
                'section_id' => null,
                'unit_id' => null,
            ]);
        }
        
        // Create MCC Chief Designation
        $mccChiefDesignation = Designation::where('name', 'MCC Chief')->first() ?? Designation::create([
            'name' => 'MCC Chief',
            'code' => 'MCC',
        ]);
        
        $mccChief = User::where('id', '!=', $user->id)
            ->where('id', '!=', $divisionChief->id)
            ->first();

        if(!$mccChief){
            $mccChief = User::factory()->create([
                'umis_employee_profile_id' => 'EMP' . rand(10000, 99999),
                'name' => 'MCC Chief',
                'email' => 'mcc.chief@example.com',
                'profile_url' => 'https://randomuser.me/api/portraits/women/' . rand(1, 99) . '.jpg',
                'is_active' => true
            ]);

            AssignedArea::create([
                'user_id'=> $mccChief->id,
                'designation_id' => $mccChiefDesignation->id,
                'division_id' => null,
                'department_id' => null,
                'section_id' => null,
                'unit_id' => null,
            ]);
        }
        
        // Create Planning Officer Designation
        $planningOfficerDesignation = Designation::where('name', 'Planning Officer')->first() ?? Designation::create([
            'name' => 'Planning Officer',
            'code' => 'PO',
        ]);
        
        $planningOfficer = User::where('id', '!=', $user->id)
            ->where('id', '!=', $divisionChief->id)
            ->where('id', '!=', $mccChief->id)
            ->first();
            

        if(!$planningOfficer){
            $planningOfficer =  User::factory()->create([
                'umis_employee_profile_id' => 'EMP' . rand(10000, 99999),
                'name' => 'Planning Officer',
                'email' => 'planning.officer@example.com',
                'profile_url' => 'https://randomuser.me/api/portraits/women/' . rand(1, 99) . '.jpg',
                'is_active' => true
            ]);

            AssignedArea::create([
                'user_id'=> $planningOfficer->id,
                'designation_id' => $planningOfficerDesignation->id,
                'division_id' => null,
                'department_id' => null,
                'section_id' => null,
                'unit_id' => null,
            ]);
        }

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
        
        for ($i = 0; $i < 5; $i++) {
            $aopApplication = AopApplication::create([
                'user_id' => $user->id,
                'division_chief_id' => $divisionChief->id,
                'mcc_chief_id' => $mccChief->id,
                'planning_officer_id' => $planningOfficer->id,
                'mission' => $missionStatements[$i],
                'status' => $statusOptions[$i],
                'has_discussed' => ($i % 2 == 0), // Alternate between true and false
                'remarks' => $remarkOptions[$i]
            ]);
            
            $aopApplications[] = $aopApplication;
        }

        // Get or create Type of Functions (strategic, core, support)
        $typeOfFunctions = TypeOfFunction::all();
        if ($typeOfFunctions->isEmpty()) {
            $typeOfFunctions = collect(['strategic', 'core', 'support'])->map(function($type) {
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

        // Create sample success indicators
        $successIndicators = [
            'SI-01' => 'Increased patient satisfaction rate by 20%',
            'SI-02' => 'Reduced waiting time by 30%',
            'SI-03' => 'Improved staff efficiency by 15%',
            'SI-04' => 'Decreased operational costs by 10%',
            'SI-05' => 'Increased service coverage by 25%'
        ];

        // Ensure we have success indicators in the database
        $createdSuccessIndicators = [];
        foreach ($successIndicators as $code => $description) {
            $createdSuccessIndicators[] = SuccessIndicator::firstOrCreate(
                ['code' => $code],
                ['description' => $description]
            );
        }

        // For each AOP application, create objectives for each type of function
        foreach ($aopApplications as $appIndex => $aopApplication) {
            // For each type of function, create application objectives
            foreach ($typeOfFunctions as $typeOfFunction) {
                $objectives = $objectivesByType[$typeOfFunction->type] ?? [];
                
                // Use different objectives based on the application index to create variety
                $startIndex = $appIndex % count($objectives);
                $selectedObjectives = array_slice($objectives, $startIndex, min(2, count($objectives)));
                
                foreach ($selectedObjectives as $index => $objectiveName) {
                    // Create function objective directly into the table
                    $functionObjective = DB::table('function_objectives')->insertGetId([
                        'type_of_function_id' => $typeOfFunction->id,
                        'objective' => $objectiveName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Create application objective
                    $applicationObjective = ApplicationObjective::create([
                        'aop_application_id' => $aopApplication->id,
                        'function_objective_id' => $functionObjective, // Use the ID directly from insertGetId
                        'objective_code' => strtoupper(substr($typeOfFunction->type, 0, 1)) . '-' . ($appIndex + 1) . '-' . ($index + 1),
                        'success_indicator_id' => $createdSuccessIndicators[array_rand($createdSuccessIndicators)]->id
                    ]);
                    
                    // For objectives not in the list, create OthersObjective
                    if ($index == 0 && $appIndex % 2 == 0) { // Only for even-indexed applications
                        OthersObjective::create([
                            'application_objective_id' => $applicationObjective->id,
                            'description' => 'Custom objective description for ' . $typeOfFunction->type . ' (Application ' . ($appIndex + 1) . ')'
                        ]);
                    }
                    
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
                'is_gad_related' => (bool)rand(0, 1),
                'cost' => rand(10000, 100000) / 100,
                'start_month' => $startMonth,
                'end_month' => $endMonth,
            ]);
            
            // Create target by Quarter (including unit of target)
            $target = Target::create([
                'activity_id' => $activity->id,
                'first_quarter' => (string)rand(1, 10),
                'second_quarter' => (string)rand(1, 10),
                'third_quarter' => (string)rand(1, 10),
                'fourth_quarter' => (string)rand(1, 10)
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
                'object_category' => $objectCategories[array_rand($objectCategories)],
                'quantity' => rand(1, 20)
            ]);
            
            // Get valid division, department, section, unit, and designation IDs
            $divisionId = $user->assignedArea->division_id ?? Division::first()->id;
            $departmentId = $user->assignedArea->department_id ?? Department::first()->id;
            $sectionId = $user->assignedArea->section_id ?? Section::first()->id;
            $unitId = $user->assignedArea->unit_id ?? Unit::first()->id;
            $designationId = $user->assignedArea->designation_id ?? Designation::first()->id;
            
            // Create responsible person (person in-charge)
            ResponsiblePerson::create([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'division_id' => $divisionId,
                'department_id' => $departmentId,
                'section_id' => $sectionId,
                'unit_id' => $unitId,
                'designation_id' => $designationId
            ]);
        }
    }
}
