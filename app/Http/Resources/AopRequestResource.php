<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ApplicationTimeline;

class AopRequestResource extends JsonResource
{
    /**
     * Get the area code safely by handling potential null values
     *
     * @return string|null
     */
    protected function getAreaCode(): ?string
    {
        try {
            if (isset($this->user) && isset($this->user->assignedArea)) {
                $details = $this->user->assignedArea->findDetails();
                if (isset($details['details']['code'])) {
                    return $details['details']['code'];
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get current user's assigned area
        $current_user = Auth::user();
        $current_user_assigned_area = $current_user?->assignedArea;
        $current_area_id = $current_user_assigned_area ? $current_user_assigned_area->id : null;

        // Determine the status based on the current user's context and timeline information
        $status = $this->status; // Default status from the application

        if ($this->relationLoaded('applicationTimelines') && $current_area_id) {
            $latest_timeline = $this->applicationTimelines->sortByDesc('created_at')->first();

            if ($latest_timeline) {
                Log::info('Timeline data for AOP Application', [
                    'aop_application_id' => $this->id,
                    'timeline_id' => $latest_timeline->id,
                    'next_area_id' => $latest_timeline->next_area_id,
                    'current_area_id' => $latest_timeline->current_area_id,
                    'current_user_area_id' => $current_area_id,
                    'status' => $latest_timeline->status,
                    'application_status' => $this->status
                ]);

                // If the application is directed to the current user's area and hasn't been processed yet
                if ($latest_timeline->next_area_id === $current_area_id &&
                    $latest_timeline->current_area_id !== $current_area_id) {
                    $status = ApplicationTimeline::STATUS_PENDING;
                }

                // If application was submitted but not yet reviewed, show as pending
                if ($latest_timeline->status === 'submitted') {
                    $status = ApplicationTimeline::STATUS_PENDING;
                }
            }
        }

        // Final status determination - if approved by final authority, keep that status
        if ($this->status === 'approved' && $this->whenLoaded('applicationTimelines', function() {
            $final_approval = $this->applicationTimelines
                ->where('next_area_id', null)
                ->where('status', 'approved')
                ->first();
            return $final_approval !== null;
        })) {
            $status = ApplicationTimeline::STATUS_APPROVED;
        }

        // If application was returned, keep that status
        if ($this->status === 'returned') {
            $status = ApplicationTimeline::STATUS_RETURNED;
        }

        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'area_code' => $this->getAreaCode(),
            'date_created' => $this->created_at,
            'date_approved' => $this->whenLoaded('applicationTimelines', function () {
                $latestTimeline = $this->applicationTimelines->sortByDesc('created_at')->first();
                return $latestTimeline ? $latestTimeline->date_approved : null;
            }),
            'aop_application_uuid' => $this->aop_application_uuid,
            'status' => $status,
            'current_approver' => $this->whenLoaded('applicationTimelines', function () {
                $latestTimeline = $this->applicationTimelines->sortByDesc('created_at')->first();
                if ($latestTimeline && $latestTimeline->next_area_id) {
                    $nextArea = $latestTimeline->nextArea()->first();
                    return $nextArea && $nextArea->user ? $nextArea->user->name : 'Pending Assignment';
                }
                return null;
            }),
            'date_returned' => $this->whenLoaded('applicationTimelines', function () {
                $returnTimeline = $this->applicationTimelines->where('status', 'returned')->sortByDesc('created_at')->first();
                return $returnTimeline ? $returnTimeline->date_returned : null;
            }),
            'year' => $this->created_at ? $this->created_at->format('Y') : null,
        ];
    }
}
