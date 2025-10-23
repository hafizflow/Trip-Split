<?php

// app/Services/ExpenseSplitService.php
namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseParticipant;

class ExpenseSplitService
{
    public function splitExpense(Expense $expense, array $participantIds)
    {
        $participantCount = count($participantIds);
        $splitAmount = round($expense->amount / $participantCount, 2);

        // Handle rounding issues - add remaining cents to first participant
        $totalSplit = $splitAmount * $participantCount;
        $remainder = $expense->amount - $totalSplit;

        foreach ($participantIds as $index => $participantId) {
            $amount = $splitAmount;

            // Add remainder to first participant
            if ($index === 0) {
                $amount += $remainder;
            }

            ExpenseParticipant::create([
                'expense_id' => $expense->id,
                'user_id' => $participantId,
                'split_amount' => $amount,
            ]);
        }
    }

    public function getUserTotalForTrip($userId, $tripId)
    {
        return ExpenseParticipant::whereHas('expense', function ($query) use ($tripId) {
            $query->where('trip_id', $tripId);
        })
            ->where('user_id', $userId)
            ->sum('split_amount');
    }

    public function getTripTotalExpenses($tripId)
    {
        return Expense::where('trip_id', $tripId)->sum('amount');
    }
}
