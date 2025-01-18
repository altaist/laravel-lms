<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'boolean'
        ];
    }
} 