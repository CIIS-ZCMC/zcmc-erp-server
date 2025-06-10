<?php

namespace App\Services;

use App\Models\AopApplication;
use App\Models\ApplicationTimeline;
use App\Models\AssignedArea;
use App\Models\Division;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class AopVisibilityService
{
    /**
     * Constants for the special identifiers
     */
    const PLANNING_UNIT_SECTION_ID = 53;
    const OMCC_DIVISION_ID = 1;

    /**
     * Get AOP applications that the user is allowed to see based on their role
     * and the current stages of the AOP applications
     *
     * @param User $user The current authenticated user
     * @param array $filters Optional filters for the query
     * @return Builder Query builder for AOP applications
     */
    public function getVisibleAopApplications(User $user, array $filters = []): Builder
    {

        $query = AopApplication::with(['user', 'applicationTimelines']);

        // If the user is the OMCC Chief, they can see all AOP requests
        if ($this->isOmccChief($user)) {
            Log::info('User is OMCC Chief, showing all AOP applications', [
                'user_id' => $user->id
            ]);
            return $this->applyFilters($query, $filters);
        }

        // Get the user's assigned area
        $assignedArea = $user->assignedArea;

        if (!$assignedArea) {
            Log::info('User does not have an assigned area, showing no applications', [
                'user_id' => $user->id
            ]);
            // Return an empty query if user has no assigned area
            return $query->whereRaw('1 = 0');
        }

        // Check if the user is in the Planning Unit
        $isInPlanningUnit = $this->isUserInPlanningUnit($assignedArea);
        if ($isInPlanningUnit) {
            Log::info('User is in Planning Unit, showing relevant applications', [
                'user_id' => $user->id,
                'area_id' => $assignedArea->id
            ]);
            // Planning Unit can see all incoming AOP requests
            return $this->applyFilters($query, $filters);
        }

        // Check if user is Division Head
        $isDivisionHead = $this->isUserDivisionHead($user, $assignedArea);
        if ($isDivisionHead) {
            Log::info('User is Division Head, showing division applications', [
                'user_id' => $user->id,
                'division_id' => $assignedArea->division_id
            ]);

            // Get all users under this division
            $areasUnderDivision = AssignedArea::where('division_id', $assignedArea->division_id)
                ->pluck('user_id')
                ->toArray();

            // Division Head can see requests from users in their division at any stage
            // and requests that currently require their action
            return $query->where(function ($q) use ($areasUnderDivision, $assignedArea, $user) {
                // Requests from users in their division
                $q->whereIn('user_id', $areasUnderDivision)
                    // OR requests waiting for their action (based on latest timeline)
                    ->orWhereHas('applicationTimelines', function ($q2) use ($user) {
                        $q2->whereRaw('id IN (
                          SELECT MAX(id)
                          FROM application_timelines
                          WHERE aop_application_id = aop_applications.id
                          GROUP BY aop_application_id
                      )')->where('next_area_id', function ($q3) use ($user) {
                            $q3->select('id')
                                ->from('assigned_areas')
                                ->where('user_id', $user->id)
                                ->limit(1);
                        });
                    });
            })
                ->when($filters, function ($q) use ($filters) {
                    return $this->applyFilters($q, $filters);
                });
        }

        // Regular User (Office/Area Staff)
        Log::info('User is regular staff, showing only their applications', [
            'user_id' => $user->id
        ]);

        // Regular users can only see their own requests and requests that require their action
        return $query->where(function ($q) use ($user) {
            // Their own requests
            $q->where('user_id', $user->id)
                // OR requests waiting for their action
                ->orWhereHas('applicationTimelines', function ($q2) use ($user) {
                    $q2->whereRaw('id IN (
                        SELECT MAX(id)
                        FROM application_timelines
                        WHERE aop_application_id = aop_applications.id
                        GROUP BY aop_application_id
                    )')->where('next_area_id', function ($q3) use ($user) {
                        $q3->select('id')
                            ->from('assigned_areas')
                            ->where('user_id', $user->id)
                            ->limit(1);
                    });
                });
        })
            ->when($filters, function ($q) use ($filters) {
                return $this->applyFilters($q, $filters);
            });
    }

    /**
     * Check if a user can view a specific AOP application
     *
     * @param User $user The current authenticated user
     * @param AopApplication $aopApplication The application to check
     * @return bool Whether the user can view this application
     */
    public function canViewAopApplication(User $user, AopApplication $aopApplication): bool
    {
        // OMCC Chief can view all applications
        if ($this->isOmccChief($user)) {
            return true;
        }

        $assignedArea = $user->assignedArea;
        if (!$assignedArea) {
            return false;
        }

        // Planning Unit can see all applications
        if ($this->isUserInPlanningUnit($assignedArea)) {
            return true;
        }

        // Creator of the application can always see it
        if ($aopApplication->user_id === $user->id) {
            return true;
        }

        // Get the latest timeline to check current stage
        $latestTimeline = $aopApplication->applicationTimelines()
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestTimeline) {
            // If no timeline exists, only the creator can see it
            return $aopApplication->user_id === $user->id;
        }

        // Check if this application is waiting for this user's action
        if ($latestTimeline->next_area_id === $assignedArea->id) {
            return true;
        }

        // Division Head can see applications from users in their division
        if ($this->isUserDivisionHead($user, $assignedArea)) {
            // Get the creator's assigned area to check division
            $creatorAssignedArea = AssignedArea::where('user_id', $aopApplication->user_id)->first();

            // If creator is in the same division, division head can see it
            if ($creatorAssignedArea && $creatorAssignedArea->division_id === $assignedArea->division_id) {
                return true;
            }
        }

        // In all other cases, deny access
        return false;
    }

    /**
     * Apply common filters to the query
     *
     * @param Builder $query The query to filter
     * @param array $filters The filters to apply
     * @return Builder The filtered query
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('mission', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Check if the user is the OMCC Chief
     *
     * @param User $user The user to check
     * @return bool Whether the user is the OMCC Chief
     */
    private function isOmccChief(User $user): bool
    {
        $omccDivision = Division::find(self::OMCC_DIVISION_ID);
        return $omccDivision && $omccDivision->head_id === $user->id;
    }

    /**
     * Check if the user belongs to the Planning Unit
     *
     * @param AssignedArea $assignedArea The user's assigned area
     * @return bool Whether the user is in the Planning Unit
     */
    private function isUserInPlanningUnit(AssignedArea $assignedArea): bool
    {

        return $assignedArea->section_id === self::PLANNING_UNIT_SECTION_ID;
    }

    /**
     * Check if the user is a Division Head
     *
     * @param User $user The user to check
     * @param AssignedArea $assignedArea The user's assigned area
     * @return bool Whether the user is a Division Head
     */
    private function isUserDivisionHead(User $user, AssignedArea $assignedArea): bool
    {
        if (!$assignedArea->division_id) {
            return false;
        }

        $division = Division::find($assignedArea->division_id);
        return $division && $division->head_id === $user->id;
    }

    /**
     * Get AOP applications that the user is allowed to see based on their role,
     * approval flow position, and application status
     *
     * @param User $user The approver (authenticated user)
     * @param array $filters Optional filters for the query
     * @return Builder Query builder for AOP applications
     */
    public function getAopApplications(User $user, array $filters = []): Builder
    {
        // Ensure user has an assigned area
        if (!$user->assignedArea) {
            Log::info('User does not have an assigned area, showing no applications', [
                'user_id' => $user->id
            ]);
            return AopApplication::whereRaw('1 = 0');
        }

        $query = AopApplication::with([
            'user',
            'applicationTimelines'
        ]);

        // Start with base query including relationship eager loading
        Log::info('Building query for AOP applications', [
            'user_id' => $user->id,
            'area_id' => $user->assignedArea->id
        ]);

        $assignedArea = $user->assignedArea;

        // Check if user is in OMCC division (but not necessarily the Chief)
        $isInOmccDivision = $assignedArea->division_id === self::OMCC_DIVISION_ID;

        Log::info('Checking if user is in OMCC division', [
            'user_id' => $user->id,
            'is_in_omcc_division' => $isInOmccDivision
        ]);

        if ($isInOmccDivision) {
            Log::info('User is in OMCC division, showing all applications plus approved from Planning', [
                'user_id' => $user->id
            ]);

            // Get the Planning Unit section ID
            $planningUnitSectionId = self::PLANNING_UNIT_SECTION_ID;

            return $query->where(function ($q) use ($user, $assignedArea, $planningUnitSectionId) {
                // Their own applications
                $q->where('user_id', $user->id)
                    ->where('status', '!=', AopApplication::STATUS_IS_DRAFT)
                  // OR applications where they are the current approver (next_area_id)
                  ->orWhereHas('applicationTimelines', function ($q2) use ($assignedArea) {
                      $q2->where('next_area_id', $assignedArea->id);
                  })
                  // OR applications they previously handled (current_area_id)
                  ->orWhereHas('applicationTimelines', function ($q2) use ($assignedArea) {
                      $q2->where('current_area_id', $assignedArea->id);
                  })
                  // OR applications that are approved AND have a timeline entry from Planning Unit
                  ->orWhere(function($q2) use ($planningUnitSectionId) {
                      $q2->where('status', AopApplication::STATUS_APPROVED)
                         ->whereHas('applicationTimelines', function($q3) use ($planningUnitSectionId) {
                             $q3->whereHas('currentArea', function($q4) use ($planningUnitSectionId) {
                                 $q4->where('section_id', $planningUnitSectionId);
                             });
                         });
                  });
            })
            ->when($filters, function ($q) use ($filters) {
                return $this->applyFilters($q, $filters);
            });
        }

        // If user is a Division Head, they can see applications from their division
        if ($this->isUserDivisionHead($user, $assignedArea)) {
            Log::info('User is Division Head, showing division applications', [
                'user_id' => $user->id,
                'division_id' => $assignedArea->division_id
            ]);

            // Get all users under this division
            $areasUnderDivision = AssignedArea::where('division_id', $assignedArea->division_id)
                ->pluck('user_id')
                ->toArray();

            // Division Head can see requests from users in their division
            // and requests they personally approved or are waiting for their   action
            return $query->where(function ($q) use ($areasUnderDivision, $user, $assignedArea) {
                // Requests from users in their division
                $q->whereIn('user_id', $areasUnderDivision)
                    ->where('status', '!=', AopApplication::STATUS_IS_DRAFT)
                  // OR requests where they are the current approver (next_area_id)
                  ->orWhereHas('applicationTimelines', function ($q2) use ($assignedArea) {
                      $q2->where('next_area_id', $assignedArea->id);
                  })
                  // OR requests where they were the previous approver (current_area_id)
                  ->orWhereHas('applicationTimelines', function ($q2) use ($assignedArea) {
                      $q2->where('current_area_id', $assignedArea->id);
                  });
            })
            ->when($filters, function ($q) use ($filters) {
                return $this->applyFilters($q, $filters);
            });
        }

        // Regular users can see their own applications and those relevant to their approval role
        Log::info('User is regular staff, showing relevant applications', [
            'user_id' => $user->id
        ]);

        return $query->where(function ($q) use ($user, $assignedArea) {
            // Their own applications
            $q->where('user_id', $user->id)
            ->where('status', '!=', AopApplication::STATUS_IS_DRAFT)
              // OR applications where they are the current approver (next_area_id)
              ->orWhereHas('applicationTimelines', function ($q2) use ($assignedArea) {
                  $q2->where('next_area_id', $assignedArea->id);
              })
              // OR applications they previously handled (current_area_id)
              ->orWhereHas('applicationTimelines', function ($q2) use ($assignedArea) {
                  $q2->where('current_area_id', $assignedArea->id);
              });
        })
        ->when($filters, function ($q) use ($filters) {
            return $this->applyFilters($q, $filters);
        });
    }
}
