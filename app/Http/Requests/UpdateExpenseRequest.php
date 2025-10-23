<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01|max:999999.99',
            'date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:1000',
            'participant_ids' => 'sometimes|required|array|min:1',
            'participant_ids.*' => 'required|integer|exists:users,id',
        ];
    }
}
