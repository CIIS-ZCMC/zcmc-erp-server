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

        $ppmp_application = $this['ppmp_application'];
        $ppmp_items = collect($this['ppmp_items'])
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
                    }) ?? null,
                    'expense_class' => $first->expense_class ?? null,
                    'item' => $item ?? null,
                    'classification' => $item->itemClassification->name ?? "",
                    'estimated_budget' => $item->estimated_budget ?? "",
                    'category' => $item->itemCategory->name ?? null,
                    'aop_quantity' => $first->total_quantity * count($first->activities) ?? 0,
                    'quantity' => $first->total_quantity ?? 0,
                    'unit' => $item->itemUnit->code ?? null,
                    'total_amount' => $first->total_amount ?? 0,
                    'target_by_quarter' => $targetByQuarter ?? null,
                    'procurement_mode' => $item->procurementMode->name ?? "",
                    'remarks' => $item->remarks ?? null,
                ];
            });

        return [
            'id' => $ppmp_application->id,
            'ppmp_application_uuid' => $ppmp_application->ppmp_application_uuid ?? null,
            'ppmp_total' => $ppmp_application->ppmp_total ?? 0,
            'status' => $ppmp_application->status ?? null,
            'remarks' => $ppmp_application->remarks ?? null,
            'year' => $ppmp_application->year ?? null,
            'user' => $ppmp_application->user ? new UserResource($ppmp_application->user) : null,
            'division_chief' => $ppmp_application->divisionChief ? new UserResource($ppmp_application->divisionChief) : null,
            'budget_officer' => $ppmp_application->budgetOfficer ? new UserResource($ppmp_application->budgetOfficer) : null,
            'aop_application' => $ppmp_application->aopApplication ? new AopApplicationResource($ppmp_application->aopApplication) : null,
            'ppmp_items' => $ppmp_items ?? [],
            'created_at' => $ppmp_application->created_at ?? null,
            'updated_at' => $ppmp_application->updated_at ?? null,
        ];
    }
}
