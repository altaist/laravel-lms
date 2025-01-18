<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditRequest;
use App\Http\Requests\CreateCreditRequest;
use App\Http\Requests\UpdateCreditRequest;
use App\Models\Credit;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;

class CreditController extends Controller
{
    public function __construct(
        private readonly CreditService $creditService
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
} 