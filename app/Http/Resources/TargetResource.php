<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TargetResource extends JsonResource
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
            'first_quarter' => $this->first_quarter,
            'second_quarter' => $this->second_quarter,
            'third_quarter' => $this->third_quarter,
            'fourth_quarter' => $this->fourth_quarter,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
