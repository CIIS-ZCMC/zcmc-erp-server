<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
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
            'object_category' => $this->object_category,
            'quantity' => $this->quantity,
            'expense_class' => $this->expense_class,
            'item' => new ItemResource($this->whenLoaded('item')), // Assuming you have an ItemResource
            'purchase_type' => new PurchaseTypeResource($this->whenLoaded('purchaseType')), // Assuming you have a PurchaseTypeResource
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
