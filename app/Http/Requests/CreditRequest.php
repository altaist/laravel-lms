<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_id' => 'required|exists:users,id',
            'user_id' => 'required|exists:users,id',
            'coin_id' => 'required|exists:coins,id',
            'amount' => 'required|integer',
            'description' => 'nullable|string',
            'creditable_type' => 'required|string',
            'creditable_id' => 'required|integer',
            'reason_id' => 'required|exists:reasons,id',
        ];
    }
} 