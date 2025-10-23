<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Trip;

class CheckTripAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $tripId = $request->route('tripId') ?? $request->route('id');

        if ($tripId) {
            $trip = Trip::find($tripId);

            if (!$trip || !$trip->isAdmin($request->user()->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can perform this action'
                ], 403);
            }
        }

        return $next($request);
    }
}
