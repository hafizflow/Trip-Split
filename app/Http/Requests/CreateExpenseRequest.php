<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExpenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'required|integer|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Expense title is required',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be at least 0.01',
            'amount.max' => 'Amount cannot exceed 999,999.99',
            'date.required' => 'Date is required',
            'participant_ids.required' => 'At least one participant is required',
            'participant_ids.min' => 'At least one participant must be selected',
            'participant_ids.*.exists' => 'One or more participants do not exist',
        ];
    }
}
