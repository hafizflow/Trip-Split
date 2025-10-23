<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
// Check if user is member of the trip
return \App\Models\Trip::find($tripId)?->isMember($user->id);
});
