<?php

namespace App\Services;

use App\Models\Credit;
use Illuminate\Database\Eloquent\Collection;

class CreditService extends BaseService
{
    public function create(array $data): Credit
    {
        return Credit::create($data);
    }

    public function update(Credit $credit, array $data): bool
    {
        return $credit->update($data);
    }

    public function delete(Credit $credit): bool
    {
        return $credit->delete();
    }

    public function getUserCredits(int $userId): Collection
    {
        return Credit::where('user_id', $userId)
            ->with(['author', 'coin', 'reason', 'creditable'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByReasonId(int $reasonId): Collection
    {
        return Credit::where('reason_id', $reasonId)
            ->with(['author', 'user', 'coin', 'creditable'])
            ->get();
    }

    public function getByCreditable(string $type, int $id): Collection
    {
        return Credit::where('creditable_type', $type)
            ->where('creditable_id', $id)
            ->with(['author', 'user', 'coin', 'reason'])
            ->get();
    }
} 