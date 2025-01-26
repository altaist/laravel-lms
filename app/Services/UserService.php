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
                    'lastName' => $data['person']['lastName'],
                    'firstName' => $data['person']['firstName'],
                    'birthDate' => $data['person']['birthDate'] ?? null,
                    'gender' => $data['person']['gender'] ?? null,
                    'shift' => $data['person']['shift'] ?? null,
                    'parentFio' => $data['person']['parentFio'],
                    'parentTel' => $data['person']['parentTel'],
                ],
            ];

            // Добавляем пароль только если он предоставлен
            if (isset($data['password'])) {
                // $fillData['password'] = $data['password'];
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