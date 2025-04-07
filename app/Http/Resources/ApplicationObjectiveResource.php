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
            'function_type' => $this->functionObjective->typeOfFunction->type ?? null,
            'objective_description' => $this->functionObjective->objective,
            'success_indicator_description' => $this->successIndicator->description ?? null,
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
