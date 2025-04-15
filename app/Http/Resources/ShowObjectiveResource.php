<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowObjectiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'activity_name' => $this->name,
            'start_month' => $this->start_month,
            'end_month' => $this->end_month,
            'target' => $this->target ? [
                'id' => $this->target->id,
                'first_quarter' => $this->target->first_quarter,
                'second_quarter' => $this->target->second_quarter,
                'third_quarter' => $this->target->third_quarter,
                'fourth_quarter' => $this->target->fourth_quarter,
            ] : null,
            'resources' => $this->resources,
            'responsible_people' => $this->responsiblePeople->map(function ($responsiblePerson) {
                return [
                    'id' => $responsiblePerson->id,
                    'user' => new UserResource($responsiblePerson->user),
                ];
            }),
            'comments' => $this->comments,
        ];
    }
}
