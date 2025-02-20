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

            // Добавляем номер карты если он предоставлен
            if (isset($data['card_number'])) {
                $fillData['card_number'] = $data['card_number'];
                $fillData['card_delivered_at'] = $data['card_delivered_at'] ?? '2025-01-01 00:00:00';
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