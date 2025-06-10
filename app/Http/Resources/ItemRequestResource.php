<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TerminologyResource;

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
            "code" => $this->code,
            "variant" => $this->variant,
            "image" => $this->image !== null ? env("SERVER_DOMAIN") . $this->image : null,
            "estimated_budget" => $this->estimated_budget,
            "unit" => $this->item_unit_id !== null ? $this->itemUnit->code : null,
            "category" => $this->item_category_id !== null ? $this->itemCategory->code : null,
            "classification" => $this->item_classification_id !== null ? $this->itemClassification->code : null,
            "item_unit" => $this->item_unit_id !== null ? new ItemUnitResource($this->itemUnit) : null,
            "item_category" => $this->item_category_id !== null ? new ItemCategoryResource($this->itemCategory) : null,
            "item_classification" => $this->item_classification_id !== null ? new ItemClassificationResource($this->itemClassification) : null,
            "item_specifications" => $this->itemSpecifications,
            "item_terminology" => $this->terminologies_category_id !== null ? new TerminologyResource($this->terminologyCategory) : null,
            // "request_by" => $this->requestedBy,
            "action_by" => $this->action_by !== null ? $this->actionBy : null,
            "status" => $this->status,
            "reason" => $this->reason,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
