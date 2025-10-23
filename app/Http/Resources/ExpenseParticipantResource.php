<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseParticipantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'expense_id' => $this->expense_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'split_amount' => (float) $this->split_amount,
        ];
    }
}
