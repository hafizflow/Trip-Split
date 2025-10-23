<?php

// app/Http/Controllers/Api/FriendController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FriendController extends Controller
{
    public function index(Request $request)
    {
        $friends = $request->user()->friends;

        return response()->json([
            'success' => true,
            'friends' => $friends
        ]);
    }

    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'friend_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Cannot send request to self
        if ($request->friend_id == $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send friend request to yourself'
            ], 400);
        }

        // Check if friendship already exists
        $existingFriendship = Friendship::where(function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)
                ->where('friend_id', $request->friend_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('user_id', $request->friend_id)
                ->where('friend_id', $request->user()->id);
        })->first();

        if ($existingFriendship) {
            if ($existingFriendship->status === 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already friends'
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Friend request already sent'
                ], 400);
            }
        }

        $friendship = Friendship::create([
            'user_id' => $request->user()->id,
            'friend_id' => $request->friend_id,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Friend request sent successfully',
            'friendship' => $friendship
        ], 201);
    }

    public function acceptRequest(Request $request, $id)
    {
        $friendship = Friendship::where('friend_id', $request->user()->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        return response()->json([
            'success' => true,
            'message' => 'Friend request accepted'
        ]);
    }

    public function rejectRequest(Request $request, $id)
    {
        $friendship = Friendship::where('friend_id', $request->user()->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend request rejected'
        ]);
    }

    public function pendingRequests(Request $request)
    {
        $requests = Friendship::where('friend_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }

    public function removeFriend(Request $request, $friendId)
    {
        $friendship = Friendship::where(function ($query) use ($request, $friendId) {
            $query->where('user_id', $request->user()->id)
                ->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($request, $friendId) {
            $query->where('user_id', $friendId)
                ->where('friend_id', $request->user()->id);
        })->where('status', 'accepted')
            ->firstOrFail();

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend removed successfully'
        ]);
    }

    public function searchUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $users = User::where('id', '!=', $request->user()->id)
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query . '%')
                    ->orWhere('email', 'like', '%' . $request->query . '%');
            })
            ->limit(20)
            ->get(['id', 'name', 'email', 'profile_picture']);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}
