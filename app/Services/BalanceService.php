<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Credit;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BalanceService extends BaseService
{
    /**
     * Обновить баланс пользователя
     */
    public function updateBalance(int $userId, int $coinId, float $amount): Balance
    {
        return Balance::updateOrCreate(
            [
                'user_id' => $userId,
                'coin_id' => $coinId,
            ],
            [
                'amount' => $amount,
            ]
        );
    }

    /**
     * Обновить кредит и баланс в одной транзакции
     */
    public function updateCreditAndBalance(
        Model $creditable,
        User $user,
        int $coinId,
        int $creditValue,
        int $reasonId = 1
    ): void {
        DB::transaction(function () use ($creditable, $user, $coinId, $creditValue, $reasonId) {
            // Создаем запись в credits
            Credit::create([
                'author_id' => $creditable->author_id,
                'user_id' => $user->id,
                'coin_id' => $coinId,
                'amount' => $creditValue,
                'creditable_type' => get_class($creditable),
                'creditable_id' => $creditable->id,
                'reason_id' => $reasonId,
            ]);

            // Обновляем баланс
            $currentBalance = Balance::where('user_id', $user->id)
                ->where('coin_id', $coinId)
                ->first();

            $newAmount = ($currentBalance ? $currentBalance->amount : 0) + $creditValue;
            
            $this->updateBalance($user->id, $coinId, $newAmount);
        });
    }

    /**
     * Обновить кредит и баланс для платежа
     */
    public function updateCreditAndBalanceForPayment(Payment $payment, User $user, int $coinId, int $creditValue): void
    {
        $this->updateCreditAndBalance(
            creditable: $payment,
            user: $user,
            coinId: $coinId,
            creditValue: $creditValue
        );
    }
} 