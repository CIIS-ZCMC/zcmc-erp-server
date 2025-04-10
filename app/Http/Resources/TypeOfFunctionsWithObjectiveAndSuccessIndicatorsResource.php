<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TypeOfFunctionsWithObjectiveAndSuccessIndicatorsResource extends JsonResource
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
            'label' => Str::ucfirst($this->type),
            'name' => $this->type,
            'code' => $this->code,
            'type' => $this->type,
            'objectives' => ObjectiveWithSuccessIndicatorsResource::collection($this->objectives),
            "meta" => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ]
            ];
    }
}
