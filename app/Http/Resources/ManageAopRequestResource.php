<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManageAopRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $applicationObjectives = $this->applicationObjectives;
        
        return [
            'id' => $this->id,
            'function_description' => $this->applicationObjectives->objective->function->description,
            'objective' => $this->applicationObjectives->objective->description,
            'success_indicator' => $this->applicationObjectives->successIndicator->description,
            'activity_count' => $this->applicationObjectives->activities->count(),
            'activities' => $this->applicationObjectives->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->name,
                    'with_comment' => $activity->comments->isNotEmpty(),
                    'is_reviewed' => $activity->is_reviewed ?? false,   // based on your actual Activity model
                ];
            }),
        ];
    }
}
