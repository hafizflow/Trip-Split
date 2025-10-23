<?php

// app/Models/Expense.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'added_by_user_id',
        'title',
        'amount',
        'date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'expense_participants')
            ->withPivot('split_amount')
            ->withTimestamps();
    }

    public function expenseParticipants()
    {
        return $this->hasMany(ExpenseParticipant::class);
    }
}
