<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createOrUpdate(array $data, ?User $user = null): User
    {
        DB::beginTransaction();
        try {
            if (!$user) {
                $user = new User();
            }
            
            // Сохраняем текущий статус карты для сравнения
            $wasCardActive = $user->card_delivered_at !== null;
            
            // Генерируем имя пользователя если не указано
            if (empty($data['name'])) {
                $lastName = $data['person']['lastName'] ?? '';
                $firstName = $data['person']['firstName'] ?? '';
                $data['name'] = trim($firstName . ' ' . $lastName);
            }

            $fillData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'] ?? UserRoleEnum::STUDENT->value,
                'person' => $data['person']
            ];

            // Добавляем пароль только если он предоставлен
            if (isset($data['password'])) {
                $fillData['password'] = $data['password'];
            }


            // Обрабатываем номер карты
            if (isset($data['card_number'])) {
                $fillData['card_number'] = $data['card_number'];
                
                if (isset($data['card_active'])) {
                    // Обрабатываем статус активности карты из формы
                    $isCardActive = filter_var($data['card_active'], FILTER_VALIDATE_BOOLEAN);
                    
                    // Если статус изменился
                    if ($wasCardActive !== $isCardActive) {
                        // Если card_active true - устанавливаем дату
                        // Если card_active false - обнуляем дату
                        $fillData['card_delivered_at'] = $isCardActive ? now() : null;
                    }
                } else if (isset($data['card_delivered_at'])) {
                    // Для импорта - используем переданную дату
                    $fillData['card_delivered_at'] = $data['card_delivered_at'];
                } else {
                    // Если номер карты указан, но нет ни статуса активности, ни даты - ставим текущую дату
                    $fillData['card_delivered_at'] = now();
                }
            } else {
                $fillData['card_number'] = null;
                $fillData['card_delivered_at'] = null;
            }

            $user->fill($fillData);
            $user->save();

            // Обновляем связь с командой
            if (isset($data['teamId'])) {
                $user->teams()->sync([$data['teamId']]);
            }

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStudentsList()
    {
        return User::students()
            ->with(['teams', 'balances'])
            ->get();
    }
} 