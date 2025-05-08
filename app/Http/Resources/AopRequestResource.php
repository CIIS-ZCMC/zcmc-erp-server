<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class AopRequestResource extends JsonResource
{
    /**
     * Get the area code safely by handling potential null values
     *
     * @return string|null
     */
    protected function getAreaCode(): ?string
    {
        try {
            if (isset($this->user) && isset($this->user->assignedArea)) {
                $details = $this->user->assignedArea->findDetails();
                if (isset($details['details']['code'])) {
                    return $details['details']['code'];
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'area_code' => $this->getAreaCode(),
            'date_created' => $this->created_at,
            'date_approved' => $this->whenLoaded('applicationTimelines', function () {
                $latestTimeline = $this->applicationTimelines->sortByDesc('created_at')->first();
                return $latestTimeline ? $latestTimeline->date_approved : null;
            }),
            'aop_application_uuid' => $this->aop_application_uuid,
            'status' => $this->status,
            'year' => $this->created_at ? $this->created_at->format('Y') : null,
        ];
    }
}
