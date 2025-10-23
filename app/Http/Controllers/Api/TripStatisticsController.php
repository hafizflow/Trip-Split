<?php

// app/Http/Controllers/Api/TripStatisticsController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Services\ExpenseSplitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripStatisticsController extends Controller
{
    protected $expenseSplitService;

    public function __construct(ExpenseSplitService $expenseSplitService)
    {
        $this->expenseSplitService = $expenseSplitService;
    }

    public function getTripStatistics(Request $request, $tripId)
    {
        $trip = Trip::with(['expenses', 'members'])->findOrFail($tripId);

        if (!$trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        $totalExpenses = $trip->expenses->sum('amount');
        $expenseCount = $trip->expenses->count();
        $memberCount = $trip->members->count();
        $averagePerPerson = $memberCount > 0 ? $totalExpenses / $memberCount : 0;

        // Get expense breakdown by member
        $memberExpenses = [];
        foreach ($trip->members as $member) {
            $totalSpent = $this->expenseSplitService->getUserTotalForTrip($member->id, $tripId);
            $memberExpenses[] = [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'total_spent' => $totalSpent,
                'profile_picture' => $member->profile_picture,
            ];
        }

        // Sort by total spent
        usort($memberExpenses, function ($a, $b) {
            return $b['total_spent'] <=> $a['total_spent'];
        });

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_expenses' => round($totalExpenses, 2),
                'expense_count' => $expenseCount,
                'member_count' => $memberCount,
                'average_per_person' => round($averagePerPerson, 2),
                'member_expenses' => $memberExpenses,
            ]
        ]);
    }

    public function getUserStatistics(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        if (!$trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        $userId = $request->user()->id;

        // Get user's total expenses
        $totalExpenses = $this->expenseSplitService->getUserTotalForTrip($userId, $tripId);

        // Get expenses breakdown
        $expenses = DB::table('expense_participants')
            ->join('expenses', 'expense_participants.expense_id', '=', 'expenses.id')
            ->where('expense_participants.user_id', $userId)
            ->where('expenses.trip_id', $tripId)
            ->select(
                'expenses.id',
                'expenses.title',
                'expenses.amount as total_amount',
                'expense_participants.split_amount',
                'expenses.date'
            )
            ->orderBy('expenses.date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_expenses' => round($totalExpenses, 2),
                'expense_count' => $expenses->count(),
                'expenses' => $expenses,
            ]
        ]);
    }

    public function getBalanceSheet(Request $request, $tripId)
    {
        $trip = Trip::with(['members', 'expenses.expenseParticipants'])->findOrFail($tripId);

        if (!$trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        $totalExpenses = $trip->expenses->sum('amount');
        $memberCount = $trip->members->count();
        $equalShare = $memberCount > 0 ? $totalExpenses / $memberCount : 0;

        $balances = [];
        foreach ($trip->members as $member) {
            $totalSpent = $this->expenseSplitService->getUserTotalForTrip($member->id, $tripId);
            $balance = $totalSpent - $equalShare;

            $balances[] = [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'total_spent' => round($totalSpent, 2),
                'equal_share' => round($equalShare, 2),
                'balance' => round($balance, 2),
                'status' => $balance > 0 ? 'owed' : ($balance < 0 ? 'owes' : 'settled'),
            ];
        }

        // Calculate who owes whom
        $settlements = $this->calculateSettlements($balances);

        return response()->json([
            'success' => true,
            'balance_sheet' => [
                'total_expenses' => round($totalExpenses, 2),
                'equal_share' => round($equalShare, 2),
                'balances' => $balances,
                'settlements' => $settlements,
            ]
        ]);
    }

    private function calculateSettlements($balances)
    {
        $owes = collect($balances)->filter(fn($b) => $b['balance'] < 0)->values();
        $owed = collect($balances)->filter(fn($b) => $b['balance'] > 0)->values();

        $settlements = [];
        $i = 0;
        $j = 0;

        while ($i < count($owes) && $j < count($owed)) {
            $debt = abs($owes[$i]['balance']);
            $credit = $owed[$j]['balance'];

            $amount = min($debt, $credit);

            $settlements[] = [
                'from_user_id' => $owes[$i]['user_id'],
                'from_user_name' => $owes[$i]['user_name'],
                'to_user_id' => $owed[$j]['user_id'],
                'to_user_name' => $owed[$j]['user_name'],
                'amount' => round($amount, 2),
            ];

            $owes[$i]['balance'] += $amount;
            $owed[$j]['balance'] -= $amount;

            if (abs($owes[$i]['balance']) < 0.01) $i++;
            if (abs($owed[$j]['balance']) < 0.01) $j++;
        }

        return $settlements;
    }
}
