<?php

// app/Events/ExpenseDeleted.php
namespace App\Events;

use App\Models\Expense;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $expenseId;
    public $tripId;
    public $title;

    public function __construct(Expense $expense)
    {
        $this->expenseId = $expense->id;
        $this->tripId = $expense->trip_id;
        $this->title = $expense->title;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('trip.' . $this->tripId);
    }

    public function broadcastWith()
    {
        return [
            'expense_id' => $this->expenseId,
            'message' => 'Expense "' . $this->title . '" was deleted',
        ];
    }
}
