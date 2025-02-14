<?php

namespace App\Services;

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

            $fillData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'person' => [
                    'last_name' => $data['person']['last_name'],
                    'first_name' => $data['person']['first_name'],
                    'birth_date' => $data['person']['birth_date'] ?? null,
                    'gender' => $data['person']['gender'] ?? null,
                    'shift' => $data['person']['shift'] ?? null,
                    'parent_fio' => $data['person']['parent_fio'],
                    'parent_tel' => $data['person']['parent_tel'],
                ],
            ];

            // Добавляем пароль только если он предоставлен
            if (isset($data['password'])) {
                $fillData['password'] = $data['password'];
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
} 