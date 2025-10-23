<?php

// app/Http/Controllers/Api/TripController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $trips = $request->user()->trips()
            ->with(['creator', 'members'])
            ->withCount('expenses')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'trips' => $trips
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $trip = Trip::create([
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => $request->user()->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Add creator as member with creator role
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

    public function show(Request $request, $id)
    {
        $trip = Trip::with(['creator', 'members', 'expenses.participants', 'expenses.addedBy'])
            ->findOrFail($id);

        // Check if user is member
        if (!$trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'trip' => $trip
        ]);
    }

    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        // Check if user is admin
        if (!$trip->isAdmin($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can update trip details'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $trip->update($request->only(['name', 'description', 'start_date', 'end_date']));

        return response()->json([
            'success' => true,
            'trip' => $trip,
            'message' => 'Trip updated successfully'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        // Only creator can delete
        if ($trip->creator_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the creator can delete this trip'
            ], 403);
        }

        $trip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trip deleted successfully'
        ]);
    }
}
