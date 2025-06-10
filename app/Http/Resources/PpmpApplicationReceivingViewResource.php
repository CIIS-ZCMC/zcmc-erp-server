<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpApplicationReceivingViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Extract the basic application details
        $data = [
            'id' => $this->id,
            'uuid' => $this->ppmp_application_uuid,
            'requester' => $this->user->name,
            'requester_area' => $this->user?->assignedArea->findDetails()['details']['code'] ?? '-',
            'created_at' => $this->created_at,
            'received_on' => $this->received_on,
            'status' => $this->status,
            'year' => $this->year,
            'total_budget' => $this->ppmpItems->sum('estimated_budget'),
            'total_items' => $this->ppmpItems->count(),
        ];
        // Format the items for display in a table
        $items = [];
        foreach ($this->ppmpItems as $index => $item) {
            $items[] = [
                'row' => $index + 1,
                'general_description' => $item->item->name ?? 'Item name',
                'classification' => $item->item->classification->name ?? 'Label name',
                'item_category' => $item->item->category->name ?? 'Label name',
                'quantity' => $item->quantity ?? 0,
                'unit' => $item->item->unit->name ?? '10s Boxes',
                'estimated_budget' => $item->estimated_budget ?? 0,
                'total_amount' => $item->quantity * $item->estimated_budget,
                // Monthly distribution - can be expanded if there's actual data
                'monthly_distribution' => [
                    'jan' => $item->jan_qty ?? 0,
                    'feb' => $item->feb_qty ?? 0,
                    'mar' => $item->mar_qty ?? 0,
                    'apr' => $item->apr_qty ?? 0,
                    'may' => $item->may_qty ?? 0,
                    'jun' => $item->jun_qty ?? 0,
                    'jul' => $item->jul_qty ?? 0,
                    'aug' => $item->aug_qty ?? 0,
                    'sep' => $item->sep_qty ?? 0,
                    'oct' => $item->oct_qty ?? 0,
                    'nov' => $item->nov_qty ?? 0,
                    'dec' => $item->dec_qty ?? 0,
                ]
            ];
        }

        $data['items'] = $items;

        return $data;
    }
}
