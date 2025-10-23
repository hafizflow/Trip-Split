<?php

// app/Models/ExpenseParticipant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'user_id',
        'split_amount',
    ];

    protected $casts = [
        'split_amount' => 'decimal:2',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
