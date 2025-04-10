<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationObjectiveResource extends JsonResource
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
            'objective_code' => $this->objective_code,

            // New: Load function type from the linked Objective's Function
            'function_type' => $this->whenLoaded('objective', function () {
                return $this->objective->function->type ?? null;
            }),

            // New: Get the description from Objective, and include others if needed
            'objective_description' => $this->whenLoaded('objective', function () {
                $description = $this->objective->description ?? null;

                if ($description === 'Others' && $this->othersObjective) {
                    return $description . ': ' . $this->othersObjective->description;
                }

                return $description;
            }),

            // Success Indicator (same logic as before)
            'success_indicator_description' => $this->whenLoaded('successIndicator', function () {
                $description = $this->successIndicator->description ?? null;

                if ($description === 'Others' && $this->otherSuccessIndicator) {
                    return $description . ': ' . $this->otherSuccessIndicator->description;
                }

                return $description;
            }),

            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
