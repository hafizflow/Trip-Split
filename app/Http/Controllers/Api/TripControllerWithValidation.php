<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Models\TripMember;
use Illuminate\Http\Request;

class TripControllerWithValidation extends Controller
{
    public function store(CreateTripRequest $request)
    {
        // Validation is automatic
        $trip = Trip::create([
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => $request->user()->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        TripMember::create([
            'trip_id' => $trip->id,
            'user_id' => $request->user()->id,
            'role' => 'creator',
        ]);

        $trip->load(['creator', 'members']);

        return response()->json([
            'success' => true,
            'trip' => $trip,
            'message' => 'Trip created successfully'
        ], 201);
    }

    public function update(UpdateTripRequest $request, $id)
    {
        $trip = Trip::findOrFail($id);

        // Authorization is automatic via Request
        $trip->update($request->validated());

        return response()->json([
            'success' => true,
            'trip' => $trip,
            'message' => 'Trip updated successfully'
        ]);
    }
}
