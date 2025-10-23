<?php

namespace App\Traits;

use App\Models\Balance;
use Illuminate\Support\Facades\DB;

trait HasBalances
{
    /**
     * Calculate and update balances for a trip
     *
     * @param int $tripId
     * @return void
     */
    public function calculateBalances($tripId)
    {
        // Get all expenses for the trip with participants
        $expenses = $this->expenses()
            ->where('trip_id', $tripId)
            ->with('expenseParticipants')
            ->get();

        // Calculate net balances
        $balances = [];

        foreach ($expenses as $expense) {
            $paidBy = $expense->added_by_user_id;
            $totalAmount = $expense->amount;

            foreach ($expense->expenseParticipants as $participant) {
                $userId = $participant->user_id;
                $share = $participant->split_amount;

                if ($userId == $paidBy) {
                    // User paid and owes themselves - net positive
                    $balances[$paidBy][$userId] = ($balances[$paidBy][$userId] ?? 0) + ($totalAmount - $share);
                } else {
                    // User owes the payer
                    $balances[$paidBy][$userId] = ($balances[$paidBy][$userId] ?? 0) + $share;
                }
            }
        }

        // Clear existing balances for this trip
        Balance::where('trip_id', $tripId)->delete();

        // Save new balances
        foreach ($balances as $creditorId => $debtors) {
            foreach ($debtors as $debtorId => $amount) {
                if ($amount > 0 && $creditorId != $debtorId) {
                    Balance::create([
                        'trip_id' => $tripId,
                        'user_id' => $debtorId,
                        'owes_to_user_id' => $creditorId,
                        'amount' => $amount,
                        'is_settled' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Get balances summary for a user in a trip
     *
     * @param int $tripId
     * @param int $userId
     * @return array
     */
    public function getBalancesSummary($tripId, $userId)
    {
        $owes = Balance::where('trip_id', $tripId)
            ->where('user_id', $userId)
            ->where('is_settled', false)
            ->with('owesTo')
            ->get();

        $owedBy = Balance::where('trip_id', $tripId)
            ->where('owes_to_user_id', $userId)
            ->where('is_settled', false)
            ->with('user')
            ->get();

        $totalOwes = $owes->sum('amount');
        $totalOwedBy = $owedBy->sum('amount');
        $netBalance = $totalOwedBy - $totalOwes;

        return [
            'owes' => $owes,
            'owed_by' => $owedBy,
            'total_owes' => (float) $totalOwes,
            'total_owed_by' => (float) $totalOwedBy,
            'net_balance' => (float) $netBalance,
        ];
    }
}
