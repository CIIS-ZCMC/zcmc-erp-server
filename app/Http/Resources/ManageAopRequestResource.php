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
        return [
            'id' => $this->id,
            'function_description' => $this->objective->typeOfFunction->type,
            'objective' => $this->objective->description,
            'success_indicator' => $this->successIndicator->description,
            'activities' => $this->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'with_comments' => $activity->comments->isNotEmpty(),
                    'is_reviewed' => $activity->is_reviewed
                ];
            }),
        ];
    }
}
