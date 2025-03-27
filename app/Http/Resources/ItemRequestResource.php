<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemRequestResource extends JsonResource
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
            "unit" => $this->itemUnit->code,
            "category" => $this->itemCategory->code,
            "classification" => $this->itemClassification->code,
            "item_unit" => $this->itemUnit,
            "item_category" => $this->itemCategory,
            "item_classification" => $this->itemClassification,
            "request_by" => $this->requestedBy,
            "action_by" => $this->actionBy,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
