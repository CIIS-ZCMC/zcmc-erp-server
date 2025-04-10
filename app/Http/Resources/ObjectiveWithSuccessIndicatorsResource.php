<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ObjectiveWithSuccessIndicatorsResource extends JsonResource
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
            'label' =>  Str::ucfirst($this->code),
            'name' =>  $this->code,
            'code' => $this->code,
            'success_indicators' => SuccessIndicatorResource::collection($this->successIndicators),
            "meta" => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ]
        ];
    }
}