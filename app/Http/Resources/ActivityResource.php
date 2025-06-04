<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ResponsiblePeopleCollection;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activity_uuid' => $this->activity_uuid,
            'activity_code' => $this->activity_code,
            'name' => $this->name,
            'is_gad_related' => $this->is_gad_related,
            'is_reviewed' => $this->is_reviewed,
            'is_reviewed_date' => $this->date_updated,
            'cost' => $this->cost,
            'start_month' => $this->start_month ? \Carbon\Carbon::parse($this->start_month)->format('Y-m') : null,
            'end_month' => $this->end_month ? \Carbon\Carbon::parse($this->end_month)->format('Y-m') : null,
            'target' => new TargetResource($this->whenLoaded('target')),
            'resources' => $this->whenLoaded('resources', function () {
                return $this->resources->map(function ($resource) {
                    $resource->activity_uuid = $this->activity_uuid; // Inject here
                    return new ResourceResource($resource);
                });
            }),

            'responsible_people' => $this->whenLoaded('responsiblePeople', function () {
                return new ResponsiblePeopleCollection($this->responsiblePeople->each(function ($person) {
                    // Pass some activity info if you want
                    $person->activity_id = $this->id;
                    $person->activity_uuid = $this->activity_uuid;
                }));
            }),
            'comments' => ActivityCommentResource::collection($this->whenLoaded('comments')) ?? [],

        ];
    }
}
