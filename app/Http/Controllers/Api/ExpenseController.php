<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Trip;
use App\Services\ExpenseSplitService;
use App\Events\ExpenseAdded;
use App\Events\ExpenseUpdated;
use App\Events\ExpenseDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected $expenseSplitService;

    public function __construct(ExpenseSplitService $expenseSplitService)
    {
        $this->expenseSplitService = $expenseSplitService;
    }

    public function index(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        if (!$trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        $expenses = $trip->expenses()
            ->with(['addedBy', 'participants'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'expenses' => $expenses
        ]);
    }

    public function store(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        // Check if user is admin
        if (!$trip->isAdmin($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can add expenses'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify all participants are trip members
        foreach ($request->participant_ids as $participantId) {
            if (!$trip->isMember($participantId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All participants must be trip members'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $expense = Expense::create([
                'trip_id' => $trip->id,
                'added_by_user_id' => $request->user()->id,
                'title' => $request->title,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
            ]);

            // Calculate and save splits
            $this->expenseSplitService->splitExpense(
                $expense,
                $request->participant_ids
            );

            $expense->load(['addedBy', 'participants']);

            // Broadcast event
            broadcast(new ExpenseAdded($expense))->toOthers();

            DB::commit();

            return response()->json([
                'success' => true,
                'expense' => $expense,
                'message' => 'Expense added successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $expenseId)
    {
        $expense = Expense::with(['trip', 'addedBy', 'participants'])
            ->findOrFail($expenseId);

        if (!$expense->trip->isMember($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this trip'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'expense' => $expense
        ]);
    }

    public function update(Request $request, $expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        $trip = $expense->trip;

        // Check if user is admin
        if (!$trip->isAdmin($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can update expenses'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'date' => 'date',
            'description' => 'nullable|string',
            'participant_ids' => 'array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $expense->update($request->only(['title', 'amount', 'date', 'description']));

            // If participants changed, recalculate splits
            if ($request->has('participant_ids')) {
                foreach ($request->participant_ids as $participantId) {
                    if (!$trip->isMember($participantId)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'All participants must be trip members'
                        ], 400);
                    }
                }

                // Delete old splits
                $expense->expenseParticipants()->delete();

                // Create new splits
                $this->expenseSplitService->splitExpense(
                    $expense,
                    $request->participant_ids
                );
            }

            $expense->load(['addedBy', 'participants']);

            // Broadcast event
            broadcast(new ExpenseUpdated($expense))->toOthers();

            DB::commit();

            return response()->json([
                'success' => true,
                'expense' => $expense,
                'message' => 'Expense updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        $trip = $expense->trip;

        // Check if user is admin
        if (!$trip->isAdmin($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete expenses'
            ], 403);
        }

        // Broadcast event before deletion
        broadcast(new ExpenseDeleted($expense))->toOthers();

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully'
        ]);
    }
}
