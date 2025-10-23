<?php

// app/Http/Controllers/Api/TripMemberController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripMember;
use App\Models\User;
use App\Events\MemberJoined;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripMemberController extends Controller
{
    public function joinByCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $trip = Trip::where('code', $request->code)->first();

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid trip code'
            ], 404);
        }

        // Check if already member
        if ($trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this trip'
            ], 400);
        }

        $tripMember = TripMember::create([
            'trip_id' => $trip->id,
            'user_id' => $request->user()->id,
            'role' => 'member',
        ]);

        // Broadcast event
        broadcast(new MemberJoined($trip, $request->user()))->toOthers();

        $trip->load(['creator', 'members']);

        return response()->json([
            'success' => true,
            'trip' => $trip,
            'message' => 'Successfully joined the trip'
        ]);
    }

    public function addMember(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        // Check if user is admin
        if (!$trip->isAdmin($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can add members'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already member
        if ($trip->isMember($request->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member'
            ], 400);
        }

        $tripMember = TripMember::create([
            'trip_id' => $trip->id,
            'user_id' => $request->user_id,
            'role' => 'member',
        ]);

        $newMember = User::find($request->user_id);
        broadcast(new MemberJoined($trip, $newMember))->toOthers();

        $trip->load(['creator', 'members']);

        return response()->json([
            'success' => true,
            'trip' => $trip,
            'message' => 'Member added successfully'
        ]);
    }

    public function updateRole(Request $request, $tripId, $userId)
    {
        $trip = Trip::findOrFail($tripId);

        // Only creator can change roles
        if ($trip->creator_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the creator can change member roles'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,member',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tripMember = TripMember::where('trip_id', $tripId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Cannot change creator role
        if ($tripMember->role === 'creator') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change creator role'
            ], 400);
        }

        $tripMember->update(['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => 'Member role updated successfully'
        ]);
    }

    public function removeMember(Request $request, $tripId, $userId)
    {
        $trip = Trip::findOrFail($tripId);

        // Check if user is admin
        if (!$trip->isAdmin($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can remove members'
            ], 403);
        }

        $tripMember = TripMember::where('trip_id', $tripId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Cannot remove creator
        if ($tripMember->role === 'creator') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove trip creator'
            ], 400);
        }

        $tripMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }
}
