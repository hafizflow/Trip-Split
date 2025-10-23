<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripControllerWithResources extends Controller
{
    public function index(Request $request)
    {
        $trips = $request->user()->trips()
            ->with(['creator', 'members'])
            ->withCount('expenses')
            ->latest()
            ->get();

        return TripResource::collection($trips);
    }

    public function show(Request $request, $id)
    {
        $trip = Trip::with(['creator', 'members', 'expenses.participants', 'expenses.addedBy'])
            ->findOrFail($id);

        if (!$trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        return new TripResource($trip);
    }
}
