<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'pay_from' => 'required|in:cash,card',
            'description' => 'nullable|string',
            'payment_at' => 'required|date'
        ]);

        $validated['author_id'] = Auth::id();
        $validated['coin_id'] = 1; // Предполагается, что у вас есть базовая валюта с ID 1

        $payment = $this->paymentService->create($validated);

        return response()->json($payment, 201);
    }
} 