<?php

namespace App\Services;

use App\Models\AssignedArea;
use App\Models\ApplicationTimeline;
use App\Models\AopApplication;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AssignedAreaResource;

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
     * Create a timeline entry for the application with appropriate next area routing based on workflow
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
            // Get the AOP application for reference
            $aopApplication = AopApplication::find($application_id);

            // ALGORITHM
            // 1. When an AOP request is submitted, it is initially stored in the application timeline
            //    with the following details:
            //    - Status: "Pending"
            //    - Current Area ID
            //    - Next Area ID
            // 2. This function is triggered when the process reaches the recorded Next Area ID.
            // 3. Once triggered, it updates the status of the AOP accordingly.
            //  id to which area will be approving next.
            
            if (!$aopApplication) {
                Log::error("Cannot create timeline - AOP application not found", [
                    'application_id' => $application_id
                ]);
                return null;
            }
           
        } catch (\Exception $e) {
            Log::error('Error creating application timeline: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

}
