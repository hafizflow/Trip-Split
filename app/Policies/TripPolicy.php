<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Trip $trip)
    {
        return $trip->isMember($user->id);
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Trip $trip)
    {
        return $trip->isAdmin($user->id);
    }

    public function delete(User $user, Trip $trip)
    {
        return $trip->creator_id === $user->id;
    }

    public function addMember(User $user, Trip $trip)
    {
        return $trip->isAdmin($user->id);
    }

    public function removeMember(User $user, Trip $trip)
    {
        return $trip->isAdmin($user->id);
    }

    public function updateMemberRole(User $user, Trip $trip)
    {
        return $trip->creator_id === $user->id;
    }
}
