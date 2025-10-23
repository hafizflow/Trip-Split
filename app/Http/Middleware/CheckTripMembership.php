<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Trip;

class CheckTripMembership
{
    public function handle(Request $request, Closure $next)
    {
        $tripId = $request->route('tripId') ?? $request->route('id');

        if ($tripId) {
            $trip = Trip::find($tripId);

            if (!$trip || !$trip->isMember($request->user()->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this trip'
                ], 403);
            }
        }

        return $next($request);
    }
}
