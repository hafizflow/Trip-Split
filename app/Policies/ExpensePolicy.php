<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, $tripId)
    {
        $trip = \App\Models\Trip::find($tripId);
        return $trip && $trip->isMember($user->id);
    }

    public function view(User $user, Expense $expense)
    {
        return $expense->trip->isMember($user->id);
    }

    public function create(User $user, $tripId)
    {
        $trip = \App\Models\Trip::find($tripId);
        return $trip && $trip->isAdmin($user->id);
    }

    public function update(User $user, Expense $expense)
    {
        return $expense->trip->isAdmin($user->id);
    }

    public function delete(User $user, Expense $expense)
    {
        return $expense->trip->isAdmin($user->id);
    }
}
