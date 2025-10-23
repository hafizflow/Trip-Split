<?php

// app/Models/Trip.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'creator_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($trip) {
            $trip->code = static::generateUniqueCode();
        });
    }

    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'trip_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function tripMembers()
    {
        return $this->hasMany(TripMember::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'trip_members')
            ->wherePivotIn('role', ['creator', 'admin']);
    }

    public function isAdmin($userId)
    {
        return $this->tripMembers()
            ->where('user_id', $userId)
            ->whereIn('role', ['creator', 'admin'])
            ->exists();
    }

    public function isMember($userId)
    {
        return $this->tripMembers()
            ->where('user_id', $userId)
            ->exists();
    }
}
