<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationTimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "aop_application_uuid" => $this->aop_application_uuid ?? null,
            "application_details" => [
                "id" => $this->id,
                "type" => 'AOP',
                "reference" => $this->aop_application_uuid
            ],
            "mission" => $this->mission,
            "status" => $this->status,
            "has_discussed" => $this->has_discussed,
            "remarks" => $this->remarks,
            "timelines" => $this->whenLoaded('applicationTimelines', function () {
                return $this->applicationTimelines->map(function ($timeline) {
                    $activity_comments = $timeline->activityComments ?? collect([]);

                    return [
                        "id" => $timeline->id,
                        "user_id" => $timeline->user->id,
                        "user" => $timeline->user->name,
                        "user_position" => $timeline->user->assignedArea->designation->name ?? null,
                        "status" => $timeline->status,
                        "remarks" => $timeline->remarks,
                        "has_comments" => $activity_comments->count() > 0,
                        "comments_count" => $activity_comments->count(),
                        "activities_with_comments" => $activity_comments->pluck('activity_id')->unique()->count(),
                        "date_created" => $timeline->date_created,
                        "date_approved" => $timeline->date_approved,
                        "date_returned" => $timeline->date_returned,
                        "date_updated" => $timeline->updated_at
                    ];
                });
            }),
            "approval_roles" => [
                "applicant" => [
                    "id" => $this->user->id ?? null,
                    "name" => $this->user->name ?? null,
                    "designation" => $this->user->assignedArea->designation->name ?? null,
                ],
                "division_chief" => [
                    "id" => $this->divisionChief->id ?? null,
                    "name" => $this->divisionChief->name ?? null,
                    "designation" => $this->divisionChief->assignedArea->designation->name ?? null,
                ],
                "planning_officer" => [
                    "id" => $this->planningOfficer->id ?? null,
                    "name" => $this->planningOfficer->name ?? null,
                    "designation" => $this->planningOfficer->assignedArea->designation->name ?? null,
                ],
                "mcc_chief" => [
                    "id" => $this->mccChief->id ?? null,
                    "name" => $this->mccChief->name ?? null,
                    "designation" => $this->mccChief->assignedArea->designation->name ?? null,
                ],
            ],
            "current_status" => $this->status ?? $this->whenLoaded('applicationTimelines', function () {
                return $this->applicationTimelines->sortByDesc('created_at')->first()->status ?? 'pending';
            }),
        ];
    }
}
