<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\AopApplication;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\PpmpSchedule;
use App\Models\User;
use App\Models\Item;
use App\Models\Division;
use App\Models\Section;
use App\Models\Activity;
use App\Models\ProcurementModes;
use Carbon\Carbon;

class PpmpApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates exactly 3 realistic PPMP Applications based on existing AOP Applications
     */
    public function run(): void
    {
        // Get existing AOP Applications
        $aopApplications = AopApplication::take(3)->get();

        if ($aopApplications->isEmpty()) {
            $this->command->error('No AOP applications found. Please run the AopApplicationSeeder first.');
            return;
        }

        // Get division chiefs and budget officers
        $divisionChief = Division::where('name', 'Hospital Operations & Patient Support Service')->first();
        if (!$divisionChief || !$divisionChief->head_id) {
            $this->command->error('Division chief not found or has no head assigned.');
            return;
        }

        $budgetOfficer = Section::where('name', 'FS: Budget Section')->first();
        if (!$budgetOfficer || !$budgetOfficer->head_id) {
            $this->command->error('Budget officer not found or has no head assigned.');
            return;
        }

        $planningOfficer = Section::where('name', 'Planning Unit')->first();
        if (!$planningOfficer || !$planningOfficer->head_id) {
            $this->command->error('Planning officer not found or has no head assigned.');
            return;
        }

        // Get procurement modes for items
        $procurementModes = ProcurementModes::all();
        if ($procurementModes->isEmpty()) {
            $this->command->error('No procurement modes found. Please run the ProcurementModeSeeder first.');
            return;
        }

        // Get items to include in PPMP
        $items = Item::all();
        if ($items->isEmpty()) {
            $this->command->error('No items found. Please run the ItemSeeder first.');
            return;
        }

        // Status and data for each of the 3 PPMP applications
        $ppmpData = [
            [
                'status' => 'approved',
                'remarks' => 'PPMP fully approved and ready for implementation. All items meet hospital standards and budget allocations.',
                'item_count' => 8,
                'title' => 'Medical Equipment and Supplies Procurement',
                'description' => 'Annual procurement of critical medical supplies and equipment for hospital operations',
                'typical_items' => ['Medical Supplies', 'Surgical Equipment', 'Laboratory Materials']
            ],
            [
                'status' => 'submitted',
                'remarks' => 'Submitted for review and approval. Awaiting division chief review for budget alignment.',
                'item_count' => 6,
                'title' => 'IT Infrastructure Upgrade',
                'description' => 'Procurement of IT equipment and software licenses for hospital information systems',
                'typical_items' => ['Computer Hardware', 'Software Licenses', 'Network Equipment']
            ],
            [
                'status' => 'draft',
                'remarks' => 'Initial PPMP draft pending completion. Currently reviewing budget allocations and priorities.',
                'item_count' => 5,
                'title' => 'Office Supplies and Administrative Materials',
                'description' => 'Procurement of general office supplies and administrative materials for hospital departments',
                'typical_items' => ['Office Supplies', 'Printing Materials', 'Filing Systems']
            ]
        ];

        // Create exactly 3 PPMP Applications with specific data
        foreach ($aopApplications as $index => $aopApplication) {
            // Use the predefined data for this PPMP
            $data = $ppmpData[$index];

            // Create PPMP Application with specific data
            $ppmpApplication = PpmpApplication::create([
                'aop_application_id' => $aopApplication->id,
                'user_id' => $aopApplication->user_id,
                'division_chief_id' => $divisionChief->head_id,
                'budget_officer_id' => $budgetOfficer->head_id,
                'planning_officer_id' => $planningOfficer->head_id,
                'ppmp_application_uuid' => substr(Str::uuid(), 0, 8),
                'ppmp_total' => 0, // Will calculate after adding items
                'is_draft' => false,
                'status' => $data['status'],
                'remarks' => $data['remarks'],
                'year' => now()->year + 1,
                'received_on' => null
            ]);

            // Record to track total budget
            $totalBudget = 0;

            // Filter items based on the application's focus
            $filteredItems = $this->filterItemsByCategory($items, $data['typical_items']);

            // If not enough items found, use random items
            if ($filteredItems->count() < $data['item_count']) {
                $filteredItems = $items->random($data['item_count']);
            } else {
                $filteredItems = $filteredItems->random(min($filteredItems->count(), $data['item_count']));
            }

            foreach ($filteredItems as $item) {
                // Select appropriate procurement mode based on item and status
                $procurementMode = $this->selectAppropriateMode($procurementModes, $item, $data['status']);

                // Determine realistic quantity and budget based on item type
                $quantity = $this->getRealisticQuantity($item);
                $estimatedBudget = $item->estimated_budget ?: $this->getRealisticBudget($item);
                $totalAmount = $quantity * $estimatedBudget;

                // Add to total budget
                $totalBudget += $totalAmount;
                $expenseClasses = ['MOOE', 'CO', 'PS'];

                // Create PPMP Item with realistic data
                $ppmpItem = PpmpItem::create([
                    'ppmp_application_id' => $ppmpApplication->id,
                    'item_id' => $item->id,
                    'procurement_mode_id' => $procurementMode->id,
                    'item_request_id' => null, // Not linking to specific request
                    'total_quantity' => $quantity,
                    'estimated_budget' => $estimatedBudget,
                    'total_amount' => $totalAmount,
                    'expense_class' => $expenseClasses[array_rand($expenseClasses)],
                    'remarks' => $this->getDetailedItemRemark($item, $data['title'])
                ]);

                // Get appropriate activities related to this PPMP's focus
                $activities = Activity::inRandomOrder()->take(2)->get();

                foreach ($activities as $activity) {
                    $activity->ppmpItems()->attach($ppmpItem->id, [
                        'remarks' => $this->getContextualActivityRemark($activity, $data['title']),
                    ]);
                }

                // Create monthly schedules appropriate for this type of item
                $this->createRealisticSchedulesForItem($ppmpItem, $data['status'], $item);
            }

            // Update the PPMP total budget
            $ppmpApplication->update([
                'ppmp_total' => $totalBudget
            ]);
        }

        $this->command->info('3 PPMP Applications seeded successfully!');
    }

    /**
     * Filter items by relevant categories
     */
    private function filterItemsByCategory($items, $relevantCategories)
    {
        return $items->filter(function ($item) use ($relevantCategories) {
            if (!$item->itemCategory) {
                return false;
            }

            $categoryName = strtolower($item->itemCategory->name);

            foreach ($relevantCategories as $category) {
                $categoryLower = strtolower($category);
                if (strpos($categoryName, $categoryLower) !== false) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Select appropriate procurement mode based on item and application status
     */
    private function selectAppropriateMode($modes, $item, $status)
    {
        // For high-value medical items in approved applications, prefer public bidding
        if (
            $status === 'approved' && $item->itemCategory &&
            strpos(strtolower($item->itemCategory->name), 'medical') !== false &&
            ($item->estimated_budget > 50000)
        ) {

            $publicBidding = $modes->first(function ($mode) {
                return strpos(strtolower($mode->name), 'public') !== false;
            });

            if ($publicBidding) {
                return $publicBidding;
            }
        }

        // For lower value items or draft status, prefer shopping or small value
        if ($status === 'draft' || ($item->estimated_budget < 10000)) {
            $shopping = $modes->first(function ($mode) {
                return strpos(strtolower($mode->name), 'shopping') !== false;
            });

            if ($shopping) {
                return $shopping;
            }
        }

        // Default to random mode if specific conditions not met
        return $modes->random();
    }

    /**
     * Get realistic quantity based on item type
     */
    private function getRealisticQuantity($item)
    {
        if (!$item->itemCategory) {
            return rand(20, 100);
        }

        $categoryName = strtolower($item->itemCategory->name);

        // Medical equipment typically ordered in smaller quantities
        if (strpos($categoryName, 'equipment') !== false) {
            return rand(2, 10);
        }

        // Consumables ordered in larger quantities
        if (
            strpos($categoryName, 'supplies') !== false ||
            strpos($categoryName, 'consumable') !== false
        ) {
            return rand(100, 500);
        }

        // Default for other items
        return rand(20, 100);
    }

    /**
     * Get realistic budget based on item type
     */
    private function getRealisticBudget($item)
    {
        if (!$item->itemCategory) {
            return rand(1000, 5000);
        }

        $categoryName = strtolower($item->itemCategory->name);

        // Medical equipment typically more expensive
        if (
            strpos($categoryName, 'equipment') !== false ||
            strpos($categoryName, 'medical') !== false
        ) {
            return rand(10000, 100000);
        }

        // IT equipment moderately expensive
        if (
            strpos($categoryName, 'it') !== false ||
            strpos($categoryName, 'computer') !== false
        ) {
            return rand(5000, 30000);
        }

        // Office supplies typically less expensive
        if (
            strpos($categoryName, 'office') !== false ||
            strpos($categoryName, 'supplies') !== false
        ) {
            return rand(500, 3000);
        }

        // Default for other items
        return rand(1000, 5000);
    }

    /**
     * Generate detailed and contextual remarks for items
     */
    private function getDetailedItemRemark($item, $context): string
    {
        $currentYear = Carbon::now()->year;

        $medicalRemarks = [
            "Essential for emergency department procedures and patient care in $currentYear fiscal year",
            "Required for surgical procedures in accordance with updated hospital protocols",
            "Needed for critical patient monitoring in ICU and emergency departments",
            "For diagnostic procedures requiring high precision measurements",
            "Necessary replacement for aging equipment to maintain quality of patient care"
        ];

        $officeRemarks = [
            "For administrative documentation across all hospital departments",
            "Required for regulatory compliance documentation and record keeping",
            "Needed for daily administrative operations and patient record management",
            "Essential office materials for hospital staff operational needs",
            "Standard supplies for administrative workflow optimization"
        ];

        $itRemarks = [
            "For upgrading hospital IT infrastructure to support EMR system",
            "Required for system maintenance and security compliance",
            "Needed for secure data management and patient information protection",
            "Essential for maintaining information security standards",
            "Required to support hospital network operations and telemedicine services"
        ];

        // Check item category to determine remark type
        if ($item->itemCategory) {
            $categoryName = strtolower($item->itemCategory->name);

            if (str_contains($categoryName, 'medical') || str_contains($categoryName, 'surgical') || str_contains($categoryName, 'laboratory')) {
                return $medicalRemarks[array_rand($medicalRemarks)];
            } elseif (str_contains($categoryName, 'office') || str_contains($categoryName, 'administrative')) {
                return $officeRemarks[array_rand($officeRemarks)];
            } elseif (str_contains($categoryName, 'it') || str_contains($categoryName, 'computer')) {
                return $itRemarks[array_rand($itRemarks)];
            }
        }

        // Contextual default remarks based on PPMP title
        if (stripos($context, 'Medical') !== false) {
            return $medicalRemarks[array_rand($medicalRemarks)];
        } elseif (stripos($context, 'IT') !== false) {
            return $itRemarks[array_rand($itRemarks)];
        } elseif (stripos($context, 'Office') !== false) {
            return $officeRemarks[array_rand($officeRemarks)];
        }

        // Truly generic fallback
        $defaultRemarks = [
            "Required for hospital operations per annual planning requirements",
            "Essential supplies for departmental functioning",
            "Standard inventory item required for $currentYear operations",
            "Necessary equipment replacement as per equipment lifecycle management",
            "Required to maintain operational standards in accordance with DOH guidelines"
        ];

        return $defaultRemarks[array_rand($defaultRemarks)];
    }

    /**
     * Generate contextual activity remarks
     */
    private function getContextualActivityRemark($activity, $context): string
    {
        $currentYear = Carbon::now()->year;

        $strategicRemarks = [
            "Aligned with hospital strategic objective for quality improvement",
            "Supports $currentYear-" . ($currentYear + 1) . " strategic goals for patient care excellence",
            "Required for implementation of strategic initiatives in healthcare delivery",
            "Essential component of hospital's strategic plan for service expansion",
            "Supports strategic quality improvement initiative across departments"
        ];

        $operationalRemarks = [
            "Critical for maintaining operational standards in patient care",
            "Required for daily operational workflow optimization",
            "Supports operational goals for efficient healthcare delivery",
            "Essential for meeting DOH operational compliance requirements",
            "Needed to ensure continuous operational capability in critical departments"
        ];

        $administrativeRemarks = [
            "Required for administrative compliance with regulatory standards",
            "Supports administrative workflow improvements in patient documentation",
            "Essential for streamlining administrative processes across departments",
            "Needed for efficient administrative management of hospital resources",
            "Part of administrative modernization initiative for hospital systems"
        ];

        // Determine context-appropriate remarks
        if (stripos($context, 'Medical') !== false) {
            return $strategicRemarks[array_rand($strategicRemarks)];
        } elseif (stripos($context, 'IT') !== false) {
            return $operationalRemarks[array_rand($operationalRemarks)];
        } elseif (stripos($context, 'Office') !== false) {
            return $administrativeRemarks[array_rand($administrativeRemarks)];
        }

        // Fallback general remarks
        $generalRemarks = [
            "Aligned with hospital's " . $currentYear . " annual objectives",
            "Supports operational goals for improved healthcare delivery",
            "Required for program implementation in accordance with DOH guidelines",
            "Essential for maintaining high-quality service delivery standards",
            "Included in departmental work plan for current fiscal year"
        ];

        return $generalRemarks[array_rand($generalRemarks)];
    }

    /**
     * Create realistic monthly procurement schedules based on item type and application status
     */
    private function createRealisticSchedulesForItem($ppmpItem, $status, $item): void
    {
        $totalQuantity = $ppmpItem->total_quantity;
        $remainingQuantity = $totalQuantity;

        // Current year and next year for schedules
        $currentYear = Carbon::now()->year;
        $nextYear = $currentYear + 1;

        // Determine appropriate distribution pattern based on item type and application status
        $patternType = $this->getAppropriatePattern($item, $status);

        // Distribution patterns
        $patterns = [
            'quarterly' => [1, 4, 7, 10], // Quarters
            'biannual' => [1, 7], // Twice a year
            'frontloaded' => [1, 2, 3], // First quarter heavy
            'backloaded' => [10, 11, 12], // Last quarter heavy
            'monthly' => range(1, 12), // Every month
            'single_delivery' => [3], // One-time delivery in Q1
            'seasonal' => [6, 7, 8] // Summer months
        ];

        $months = $patterns[$patternType];

        // Distribute quantity across months
        $distributedQuantities = [];

        if ($patternType === 'monthly') {
            // Even distribution for monthly pattern
            $baseQuantity = floor($totalQuantity / 12);
            $remainder = $totalQuantity % 12;

            for ($month = 1; $month <= 12; $month++) {
                $quantity = $baseQuantity;
                if ($remainder > 0) {
                    $quantity += 1;
                    $remainder--;
                }
                $distributedQuantities[$month] = $quantity;
            }
        } elseif ($patternType === 'single_delivery') {
            // All quantity in a single month
            $distributedQuantities[$months[0]] = $totalQuantity;

            // Zero for all other months
            for ($month = 1; $month <= 12; $month++) {
                if ($month !== $months[0]) {
                    $distributedQuantities[$month] = 0;
                }
            }
        } else {
            // Distribute across selected months
            $numMonths = count($months);

            foreach ($months as $index => $month) {
                if ($index === count($months) - 1) {
                    // Last month gets remaining quantity
                    $distributedQuantities[$month] = $remainingQuantity;
                } else {
                    // Strategic distribution for other months
                    if ($patternType === 'frontloaded') {
                        // More in earlier months
                        $maxForThisMonth = min($remainingQuantity, ceil($totalQuantity / $numMonths) * 1.8);
                        $quantity = rand(ceil($maxForThisMonth / 2), $maxForThisMonth);
                    } elseif ($patternType === 'backloaded') {
                        // Less in earlier months
                        $maxForThisMonth = min($remainingQuantity, ceil($totalQuantity / $numMonths) * 0.7);
                        $quantity = rand(ceil($maxForThisMonth / 3), $maxForThisMonth);
                    } else {
                        // Balanced distribution
                        $maxForThisMonth = min($remainingQuantity, ceil($totalQuantity / $numMonths) * 1.3);
                        $quantity = rand(ceil($maxForThisMonth / 2), $maxForThisMonth);
                    }

                    $distributedQuantities[$month] = $quantity;
                    $remainingQuantity -= $quantity;
                }
            }

            // Set zero for months not in the pattern
            for ($month = 1; $month <= 12; $month++) {
                if (!isset($distributedQuantities[$month])) {
                    $distributedQuantities[$month] = 0;
                }
            }
        }

        // Create schedule entries for all months with realistic year allocation
        for ($month = 1; $month <= 12; $month++) {
            // For approved items, use proper fiscal year planning
            // For others, simplify with current/next year split
            if ($status === 'approved') {
                $scheduleYear = ($month >= 1 && $month <= 3) ? $currentYear : $nextYear;
            } else {
                $scheduleYear = ($month <= 6) ? $currentYear : $nextYear;
            }

            PpmpSchedule::create([
                'ppmp_item_id' => $ppmpItem->id,
                'month' => $month,
                'year' => $scheduleYear,
                'quantity' => $distributedQuantities[$month] ?? 0
            ]);
        }
    }

    /**
     * Determine appropriate distribution pattern based on item type and application status
     */
    private function getAppropriatePattern($item, $status): string
    {
        if (!$item->itemCategory) {
            return $status === 'approved' ? 'quarterly' : 'monthly';
        }

        $categoryName = strtolower($item->itemCategory->name);

        // Medical equipment - typically one-time or quarterly delivery
        if (strpos($categoryName, 'equipment') !== false && $item->estimated_budget > 20000) {
            return 'single_delivery';
        }

        // Medical supplies - regular quarterly or monthly needs
        if (strpos($categoryName, 'medical') !== false && strpos($categoryName, 'supplies') !== false) {
            return $status === 'approved' ? 'quarterly' : 'monthly';
        }

        // IT equipment - typically front-loaded or one-time
        if (strpos($categoryName, 'it') !== false || strpos($categoryName, 'computer') !== false) {
            return 'frontloaded';
        }

        // Office supplies - regular monthly needs
        if (strpos($categoryName, 'office') !== false && strpos($categoryName, 'supplies') !== false) {
            return 'monthly';
        }

        // Default patterns based on status
        if ($status === 'approved') {
            return 'quarterly';
        } elseif ($status === 'submitted') {
            return 'biannual';
        } else {
            return 'monthly';
        }
    }
}
