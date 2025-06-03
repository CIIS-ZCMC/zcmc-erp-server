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
     * Get approved AOP applications that the approver has approved
     *
     * @param User $user The approver (authenticated user)
     * @param array $filters Optional filters for the query
     * @return Builder Query builder for approved AOP applications
     */
    public function getAopApplications(User $user, array $filters = []): Builder
    {
        $query = AopApplication::with([
            'user',
            'applicationTimelines'
        ])->where(function ($query)  use ($user) {
            $query->whereHas('applicationTimelines', function ($subQuery) use ($user) {
                $subQuery->where('next_area_id', $user->assignedArea->id)
                    ->orderByDesc('created_at')
                    ->latest();
            });
        });


        Log::info('Querying approved AOP applications', [
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $assignedArea = $user->assignedArea;

        // If user is OMCC Chief or in Planning Unit, they can see all approved applications
        if ($this->isOmccChief($user) || ($assignedArea && $this->isUserInPlanningUnit($assignedArea))) {
            Log::info('User is OMCC Chief or Planning Unit member, showing all approved AOP applications', [
                'user_id' => $user->id
            ]);
            return $this->applyFilters($query, $filters);
        }
//
//        // If user is a Division Head, they can see approved applications from their division
//        if ($assignedArea && $this->isUserDivisionHead($user, $assignedArea)) {
//            Log::info('User is Division Head, showing division approved applications', [
//                'user_id' => $user->id,
//                'division_id' => $assignedArea->division_id
//            ]);
//
//            // Get all users under this division
//            $areasUnderDivision = AssignedArea::where('division_id', $assignedArea->division_id)
//                ->pluck('user_id')
//                ->toArray();
//
//            // Division Head can see approved requests from users in their division
//            // and requests they personally approved
//            return $query->where(function ($q) use ($areasUnderDivision, $user) {
//                // Approved requests from users in their division
//                $q->whereIn('user_id', $areasUnderDivision)
//                  // OR requests they participated in approving
//                  ->orWhereHas('applicationTimelines', function ($q2) use ($user) {
//                      $q2->where('user_id', $user->id);
////                         ->whereIn('action', ['approved', 'endorsed']);
//                  });
//            })
//            ->when($filters, function ($q) use ($filters) {
//                return $this->applyFilters($q, $filters);
//            });
//        }
//
//        // Regular users can see their own approved applications and those they approved
//        Log::info('User is regular staff, showing only their approved applications', [
//            'user_id' => $user->id
//        ]);

//        return $query->where(function ($q) use ($user) {
//            // Their own approved applications
//            $q->where('user_id', $user->id);
//              // OR applications they participated in approving
//              ->orWhereHas('applicationTimelines', function ($q2) use ($user) {
//                  $q2->where('user_id', $user->id)
//                     ->whereIn('action', ['approved', 'endorsed']);
//              });
//        })
        return $query
        ->when($filters, function ($q) use ($filters) {
            return $this->applyFilters($q, $filters);
        });
    }
}
