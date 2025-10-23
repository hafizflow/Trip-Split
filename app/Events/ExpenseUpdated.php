<?php

// app/Events/ExpenseUpdated.php
namespace App\Events;

use App\Models\Expense;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense->load(['addedBy', 'participants']);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('trip.' . $this->expense->trip_id);
    }

    public function broadcastWith()
    {
        return [
            'expense' => $this->expense,
            'message' => 'Expense "' . $this->expense->title . '" was updated',
        ];
    }
}
