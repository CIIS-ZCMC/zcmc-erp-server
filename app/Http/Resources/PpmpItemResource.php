<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpItemResource extends JsonResource
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
            'ppmp_application' => $this->ppmp_application,
            'item' => $this->item,
            'procurement_modes' => $this->procurement_modes,
            'item_request' => $this->item_request,
            'total_quantity' => $this->total_quantity,
            'estimated_budget' => $this->estimated_budget,
            'total_amount' => $this->total_amount,
            'remarks' => $this->remarks,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
            ],
        ];
    }
}
