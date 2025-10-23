<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TripMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'role' => $this->role,
            'joined_at' => $this->joined_at->toISOString(),
        ];
    }
}
