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
        // For debugging - log the resource
        \Log::info('Resource data:', [
            'has_other_obj' => $this->whenLoaded('otherObjective') ? 'Yes' : 'No',
            'other_obj' => $this->otherObjective
        ]);
        
        return [
            'id' => $this->id,
            'aop_application_id' => $this->aop_application_id,
            'function_description' => $this->objective->typeOfFunction->type,
            'objective' => $this->objective->description,
            'other_objective' => $this->whenLoaded('otherObjective') ? $this->otherObjective->description : null,
            'success_indicator' => $this->successIndicator->description,
            'other_success_indicator' => $this->whenLoaded('otherSuccessIndicator') ? $this->otherSuccessIndicator->description : null,
            'is_editable' => $this->whenLoaded('otherObjective') && $this->whenLoaded('otherSuccessIndicator') ? true : false,
            'activities' => $this->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'with_comments' => $activity->comments->isNotEmpty(),
                    'is_reviewed' => $activity->is_reviewed
                ];
            }),
        ];
    }
}
