<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            "code" => $this->code,
            "variant" => $this->variant,
            "image" => $this->image !== null? env("SERVER_DOMAIN").$this->image: null,
            "estimated_budget" => $this->estimated_budget,
            "unit" => $this->itemUnit->code,
            "category" => $this->itemCategory->code,
            "classification" => $this->itemClassification->code,
            "item_unit" => $this->itemUnit,
            "item_category" => $this->itemCategory,
            "item_classification" => $this->itemClassification,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
