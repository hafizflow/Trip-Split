<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Trips created by user
    public function createdTrips()
    {
        return $this->hasMany(Trip::class, 'creator_id');
    }

    // All trips user is part of
    public function trips()
    {
        return $this->belongsToMany(Trip::class, 'trip_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // Trip memberships
    public function tripMemberships()
    {
        return $this->hasMany(TripMember::class);
    }

    // Expenses added by user
    public function addedExpenses()
    {
        return $this->hasMany(Expense::class, 'added_by_user_id');
    }

    // Expenses user is participant in
    public function expenses()
    {
        return $this->belongsToMany(Expense::class, 'expense_participants')
            ->withPivot('split_amount')
            ->withTimestamps();
    }

    // Friends
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
            ->wherePivot('status', 'accepted')
            ->withTimestamps();
    }

    // Friend requests sent
    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id')
            ->where('status', 'pending');
    }

    // Friend requests received
    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id')
            ->where('status', 'pending');
    }
}
