<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'trip_id' => $this->trip_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'owes_to' => new UserResource($this->whenLoaded('owesTo')),
            'amount' => (float) $this->amount,
            'is_settled' => (bool) $this->is_settled,
            'settled_at' => $this->settled_at?->toISOString(),
        ];
    }
}
