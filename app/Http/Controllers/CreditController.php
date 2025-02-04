<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditRequest;
use App\Http\Requests\CreateCreditRequest;
use App\Http\Requests\UpdateCreditRequest;
use App\Models\Credit;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Balance;
use App\Services\BalanceService;

class CreditController extends Controller
{
    public function __construct(
        private readonly CreditService $creditService,
        private readonly BalanceService $balanceService
    ) {}

    public function index(): JsonResponse
    {
        $credits = Credit::with(['author', 'user', 'coin', 'reason', 'creditable'])->get();
        return response()->json($credits);
    }

    public function store(CreditRequest $request): JsonResponse
    {
        $credit = $this->creditService->create($request->validated());
        return response()->json($credit, 201);
    }

    public function show(Credit $credit): JsonResponse
    {
        return response()->json($credit->load(['author', 'user', 'coin', 'reason', 'creditable']));
    }

    public function update(CreditRequest $request, Credit $credit): JsonResponse
    {
        $this->creditService->update($credit, $request->validated());
        return response()->json($credit->fresh());
    }

    public function destroy(Credit $credit): JsonResponse
    {
        $this->creditService->delete($credit);
        return response()->json(null, 204);
    }

    public function getUserCredits(int $userId): JsonResponse
    {
        $credits = $this->creditService->getUserCredits($userId);
        return response()->json($credits);
    }

    public function getByReason(int $reasonId): JsonResponse
    {
        $credits = $this->creditService->getByReasonId($reasonId);
        return response()->json($credits);
    }

    public function getByCreditable(string $type, int $id): JsonResponse
    {
        $credits = $this->creditService->getByCreditable($type, $id);
        return response()->json($credits);
    }

    public function storeManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'coin_id' => 'required|exists:coins,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'reason_id' => 'required|integer'
        ]);

        DB::transaction(function () use ($validated) {
            $credit = $this->creditService->create([
                'author_id' => Auth::id(),
                'user_id' => $validated['user_id'],
                'coin_id' => $validated['coin_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'reason_id' => $validated['reason_id'],
                'creditable_type' => User::class,
                'creditable_id' => Auth::id(),
            ]);

            // Обновляем баланс
            $currentBalance = Balance::where('user_id', $validated['user_id'])
                ->where('coin_id', $validated['coin_id'])
                ->first();

            $newAmount = ($currentBalance ? $currentBalance->amount : 0) + $validated['amount'];
            
            $this->balanceService->updateBalance(
                $validated['user_id'],
                $validated['coin_id'],
                $newAmount
            );

            return $credit;
        });

        return response()->json(['message' => 'Credit created successfully'], 201);
    }
} 