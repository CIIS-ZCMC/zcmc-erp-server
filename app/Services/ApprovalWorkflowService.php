<?php

namespace App\Services;


use App\Models\AssignedArea;
use App\Models\ApplicationTimeline;
use App\Models\AopApplication;
use Illuminate\Support\Facades\Log;


class ApprovalWorkflowService
{

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the workflow stages for AOP approval
     * 
     * @return array
     */
    public function getAopWorkflowStages()
    {
        return [
            'planning' => [
                'name' => 'Planning Unit',
                'order' => 1,
                'type' => 'unit',
                'description' => 'Head of Planning Unit'
            ],
            'division_chief' => [
                'name' => 'Division Chief',
                'order' => 2,
                'type' => 'division',
                'description' => 'Heads of Division offices'
            ],
            'medical_center_chief' => [
                'name' => 'Medical Center Chief',
                'order' => 3,
                'type' => 'division', // Assuming MCC is at division level
                'description' => 'Chief of OMCC'
            ],
            'budget_officer' => [
                'name' => 'Budget Officer',
                'order' => 4,
                'type' => 'unit',
                'description' => 'Head of Budget'
            ]
        ];
    }

    /**
     * Get the area ID for a specific workflow stage
     * 
     * @param string $stage_name
     * @return int|null
     */
    public function getAreaIdByWorkflowStage($stage_name)
    {
        // This would typica                                             lly be a database lookup. For demonstration, 
        // we're using a simple switch statement
        switch ($stage_name) {
            case 'planning':
                $area = AssignedArea::where('section_id', function($query) {
                    $query->select('id')
                        ->from('sections')
                        ->where('name', 'like', '%Planning Unit%');
                })
                ->first();
                break;
            case 'division_chief':
                // This would need to be the specific division chief area for the application
                // For now, just returning any division chief
                $area = AssignedArea::where('division_id', function($query) {
                    $query->select('id')
                        ->from('divisions')
                        ->where('name', 'like', '%Division Chief%');
                })
                ->first();
                break;
            case 'medical_center_chief':
                $area = AssignedArea::where('division_id', function($query) {
                    $query->select('id')
                        ->from('divisions')
                        ->where('name', 'like', '%Medical Center Chief%');
                })
                ->first();
                break;
            case 'budget_officer':
                $area = AssignedArea::where('section_id', function($query) {
                    $query->select('id')
                        ->from('sections')
                        ->where('name', 'like', '%Budget%');
                })
                ->first();
                break;
            default:
                return null;
        }

        return $area ? $area->id : null;
    }

    /**
     * Get the next area in the workflow
     * 
     * @param int $current_area_id
     * @param string $status 'approved' or 'returned'
     * @param int $application_id The AOP application ID
     * @return int|null
     */
    public function getNextAreaId($current_area_id, $status, $application_id = null)
    {
        // Get the current area
        $curreent_area = AssignedArea::find($current_area_id);
        if (!$curreent_area) {
            return null;
        }

        // Get all workflow stages
        $workflow_stages = $this->getAopWorkflowStages();
        
        // Find the current stage by matching area attributes
                             $current_stage_key = null;
        foreach ($workflow_stages as $key => $stage) {
            if (stripos($curreent_area->name, $stage['name']) !== false && 
                $curreent_area->type == $stage['type']) {
                $current_stage_key = $key;
                break;
            }
        }

        if (!$current_stage_key) {
            return null;
        }

        // If status is 'returned', return to the applicant's area
        if ($status === 'returned' && $application_id) {
            $application = AopApplication::find($application_id);
            if ($application && $application->user) {
                // Get the applicant's area (would need to be implemented based on your user-area relationship)
                // For now, just returning the first area in the workflow
                return $this->getAreaIdByWorkflowStage(array_key_first($workflow_stages));
                                                         }
        }

        // If status is 'approved', move to next stage
        if ($status === 'approved') {
            // Get the next stage by order
            $currentOrder = $workflow_stages[$current_stage_key]['order'];
            
            foreach ($workflow_stages as $key => $stage) {
                if ($stage['order'] == $currentOrder + 1) {
                    return $this->getAreaIdByWorkflowStage($key);
                }
            }
                                                     }

        // If no next area found or unclear status, stay at current area
        return $current_area_id;
    }

    /**
     * Create a timeline entry for the application
     * 
     * @param int $application_id
     * @param int $userId
     * @param int $current_area_id
     * @param string $status
     * @param string $remarks
     * @return ApplicationTimeline|null
     */
    public function createApplicationTimeline($application_id, $userId, $current_area_id, $status, $remarks = null)
    {
        try {
            $next_area_id = $this->getNextAreaId($current_area_id, $status, $application_id);
            
            $date_approved = null;
            $date_returned = null;
            
            if ($status === 'approved') {
                $date_approved = now();
            } elseif ($status === 'returned') {
                $date_returned = now();
            }
            
            return ApplicationTimeline::create([
                'aop_application_id' => $application_id,
                'user_id' => $userId,
                'current_area_id' => $current_area_id,
                'next_area_id' => $next_area_id,
                'status' => $status,
                'remarks' => $remarks,
                'date_created' => now(),
                'date_approved' => $date_approved,
                'date_returned' => $date_returned,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating application timeline: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a user is authorized for an action on the current workflow stage
     * 
     * @param int $user_id
                          * @param int $area_id
     * @return bool
     */
    public function isUserAuthorizedForArea($user_id, $area_id)
    {
        $area = AssignedArea::find($area_id);
        
        if (!$area) {
            return false;
        }
        
        // Implement the logic to determine if the user is authorized for this area
        // This would depend on how your user-area relationship is set up
                             
        // For now, simple placeholder implementation
        return true;
    }
}
