<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'order_number' => 'required|string|unique:orders,order_number',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'shipping_address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'required|exists:addresses,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ];
    }
} 