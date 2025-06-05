<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpApplicationReceivingListResource extends JsonResource
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
            'requester' => $this->user->name ?? 'Unknown User',
            'requester_area' => $this->user->assignedArea->findDetails()['details'] ?? 'Unknown Area',
            'received_on' => $this->created_at->format('Y-m-d H:i:s'),
            'total_items' => $this->ppmpItems->count() ?? 0,
            'total_budget' => $this->ppmpItems->sum('estimated_budget') ?? 0,
            'status' => $this->status,
            'year' => $this->year,
        ];
    }
}
