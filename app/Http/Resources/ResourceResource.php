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
            'quantity' => $this->quantity,
            'expense_class' => $this->expense_class,
            'item' => $this->relationLoaded('item') && $this->item
                ? new ItemResource($this->item, $this->activity_uuid, $this->quantity)
                : null,
            'purchase_type' => new PurchaseTypeResource($this->whenLoaded('purchaseType')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
