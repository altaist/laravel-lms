<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'tax_number' => 'required|string|unique:companies,tax_number',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:companies,email'
        ];
    }
} 