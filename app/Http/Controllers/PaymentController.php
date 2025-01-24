<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'credit_amount' => 'required|integer|min:0',
            'pay_from' => 'required|in:cash,card',
            'description' => 'nullable|string',
            'payment_at' => 'required|date',
            'coin_id' => 'required|exists:coins,id'
        ]);

        $validated['author_id'] = Auth::id();

        $payment = $this->paymentService->processPayment($validated);

        return response()->json($payment, 201);
    }
} 