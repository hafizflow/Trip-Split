<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'added_by' => new UserResource($this->whenLoaded('addedBy')),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'participant_splits' => $this->when(
                $this->relationLoaded('expenseParticipants'),
                function () {
                    return $this->expenseParticipants->map(function ($participant) {
                        return [
                            'user_id' => $participant->user_id,
                            'split_amount' => (float) $participant->split_amount,
                        ];
                    });
                }
            ),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
