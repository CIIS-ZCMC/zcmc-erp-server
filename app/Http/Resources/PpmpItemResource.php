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
            'ppmp_application_id' => $this->ppmp_application_id,
            'item' => new ItemResource($this->item),
            'procurement_mode' => new ProcurementModeResource($this->procurementMode),
            'item_request' => $this->item_request,
            'total_quantity' => $this->total_quantity,
            'estimated_budget' => $this->estimated_budget,
            'total_amount' => $this->total_amount,
            'remarks' => $this->remarks,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
