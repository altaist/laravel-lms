<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PaymentService extends BaseService
{
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
        ->with(['user', 'teams'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function getTeamsPayments(array $teamIds)
    {
        return Payment::whereHas('user.teams', function ($query) use ($teamIds) {
            $query->whereIn('teams.id', $teamIds);
        })
        ->with(['user'])
        ->orderBy('payment_at', 'desc')
        ->get();
    }
} 