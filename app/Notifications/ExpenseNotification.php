<?php

// app/Notifications/ExpenseNotification.php
namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $expense;
    protected $type;

    public function __construct(Expense $expense, $type = 'added')
    {
        $this->expense = $expense;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $messages = [
            'added' => $this->expense->addedBy->name . ' added a new expense: ' . $this->expense->title,
            'updated' => 'Expense "' . $this->expense->title . '" was updated',
            'deleted' => 'Expense "' . $this->expense->title . '" was deleted',
        ];

        return [
            'type' => 'expense_' . $this->type,
            'expense_id' => $this->expense->id,
            'trip_id' => $this->expense->trip_id,
            'title' => $this->expense->title,
            'amount' => $this->expense->amount,
            'message' => $messages[$this->type] ?? 'Expense notification',
        ];
    }
}
