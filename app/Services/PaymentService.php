<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;

class PaymentService extends BaseService
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {}

    public function create(array $data): Payment
    {
        return Payment::create([
            'user_id' => $data['user_id'],
            'author_id' => $data['author_id'],
            'coin_id' => $data['coin_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'pay_from' => $data['pay_from'],
            'payment_at' => $data['payment_at']
        ]);
    }

    public function update(Payment $payment, array $data): bool
    {
        return $payment->update($data);
    }

    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }

    public function getUsersByPeriod(string $period): Collection
    {
        $date = match ($period) {
            'day' => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            default => Carbon::now()->subMonth(),
        };

        return Payment::where('payment_at', '>=', $date)
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique();
    }

    public function getDailyPayers(): Collection
    {
        return $this->getUsersByPeriod('day');
    }

    public function getWeeklyPayers(): Collection
    {
        return $this->getUsersByPeriod('week');
    }

    public function getMonthlyPayers(): Collection
    {
        return $this->getUsersByPeriod('month');
    }

    public function getPaymentsByPeriod(string $period): Collection
    {
        $date = match ($period) {
            'day' => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            default => Carbon::now()->subMonth(),
        };

        return Payment::where('payment_at', '>=', $date)
            ->with('user')
            ->get();
    }

    public function getDailyPayments(): Collection
    {
        return $this->getPaymentsByPeriod('day');
    }

    public function getWeeklyPayments(): Collection
    {
        return $this->getPaymentsByPeriod('week');
    }

    public function getMonthlyPayments(): Collection
    {
        return $this->getPaymentsByPeriod('month');
    }

    public function getTeamPayments(int $teamId)
    {
        return Payment::whereHas('user.teams', function ($query) use ($teamId) {
            $query->where('teams.id', $teamId);
        })
        ->with(['user.teams'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function getTeamsPayments(array $teamIds)
    {
        return Payment::whereHas('user.teams', function ($query) use ($teamIds) {
            $query->whereIn('teams.id', $teamIds);
        })
        ->with(['user.teams'])
        ->orderBy('payment_at', 'desc')
        ->get();
    }

    /**
     * Создать платеж и обновить баланс пользователя
     */
    public function processPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = $this->create($data);
            
            $this->balanceService->updateCreditAndBalance(
                creditable: $payment,
                userId: $payment->user_id,
                creditCoinId: 2,
                creditValue: $data['credit_amount']
            );
            
            return $payment;
        });
    }

    /**
     * Получить все платежи конкретного студента
     */
    public function getUserPayments(int $userId): Collection
    {
        return Payment::where('user_id', $userId)
            ->with(['user', 'coin'])
            ->orderBy('payment_at', 'desc')
            ->get();
    }
} 