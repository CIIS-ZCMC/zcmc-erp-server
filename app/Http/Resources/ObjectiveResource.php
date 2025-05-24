<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObjectiveResource extends JsonResource
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
            'function' => $this->typeOfFunction,
            'objective' => [
                "id" => $this->id,
                "code" => $this->code,
                "description" => $this->description,
            ],
            'success_indicator' => SuccessIndicatorResource::collection($this->successIndicators),
            "meta" => [
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ]
        ];
    }
}
