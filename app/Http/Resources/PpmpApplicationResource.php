<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $monthMap = [
            '1' => 'jan',
            '2' => 'feb',
            '3' => 'mar',
            '4' => 'apr',
            '5' => 'may',
            '6' => 'jun',
            '7' => 'jul',
            '8' => 'aug',
            '9' => 'sep',
            '10' => 'oct',
            '11' => 'nov',
            '12' => 'dec',
        ];

        $ppmp_items = collect($this->ppmpItems)
            ->groupBy(fn($item) => $item->item->id)
            ->values()
            ->map(function ($group) use ($monthMap) {
                $first = $group->first();
                $item = $first->item;


                // Build default target_by_quarter
                $targetByQuarter = collect([
                    'jan' => 0,
                    'feb' => 0,
                    'mar' => 0,
                    'apr' => 0,
                    'may' => 0,
                    'jun' => 0,
                    'jul' => 0,
                    'aug' => 0,
                    'sep' => 0,
                    'oct' => 0,
                    'nov' => 0,
                    'dec' => 0,
                ])->merge(
                        $first->ppmpSchedule
                            ->groupBy('month')
                            ->mapWithKeys(function ($group, $month) use ($monthMap) {
                            return [
                                $monthMap[$month] => $group->sum('quantity')
                            ];
                        })
                    );

                return [
                    'id' => $item->id,
                    'item_code' => $item->code,
                    'activities' => $first->activities->map(function ($activity) {
                        return [
                            'activity_id' => $activity->id,
                            'activity_code' => $activity->activity_code,
                            'activity_name' => $activity->name,
                        ];
                    }),
                    'expense_class' => 'MOOE',
                    'description' => $item->name,
                    'classification' => $item->itemClassification->name ?? "",
                    'estimated_budget' => $item->estimated_budget ?? "",
                    'category' => $item->itemCategory->name,
                    'aop_quantity' => $first->total_quantity,
                    'unit' => $item->itemUnit->code,
                    'total_amount' => $first->total_amount,
                    'target_by_quarter' => $targetByQuarter,
                    'procurement_mode' => "" ?? $item->procurementMode->name,
                    'remarks' => $item->remarks,
                ];
            });

        return [
            'id' => $this->id,
            'ppmp_application_uuid' => $this->ppmp_application_uuid,
            'ppmp_total' => $this->ppmp_total,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->user ? new UserResource($this->user) : null,
            'division_chief' => $this->divisionChief ? new UserResource($this->divisionChief) : null,
            'budget_officer' => $this->budgetOfficer ? new UserResource($this->budgetOfficer) : null,
            'aop_application' => $this->aopApplication ? new AopApplicationResource($this->aopApplication) : null,
            'ppmp_items' => $ppmp_items,
        ];
    }
}
