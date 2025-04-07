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
            'function_type' => $this->whenLoaded('functionObjective', function () {
                return $this->functionObjective->function->type ?? null; // Get type from functions table
            }),
            'objective_description' => $this->whenLoaded('functionObjective', function () {
                // Get the objective description
                $description = $this->functionObjective->objective->description ?? null;

                // If description is "Others", combine with others_objective description
                if ($description === 'Others' && $this->othersObjective) {
                    return $description . ': ' . $this->othersObjective->description;
                }

                return $description;
            }),
            'success_indicator_description' => $this->whenLoaded('successIndicator', function () {
                return $this->successIndicator->description ?? null;
            }),
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
