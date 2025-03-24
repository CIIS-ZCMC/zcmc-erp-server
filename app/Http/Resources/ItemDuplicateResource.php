<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemDuplicateResource extends JsonResource
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
            "name" => $this->name,
            "estimated_budget" => $this->estimated_budget,
            "unit" => $this->itemUnit,
            "category" => $this->itemCategory,
            "classification" => $this->itemClassification,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
