<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Определяет, может ли пользователь генерировать ссылки для входа
     */
    public function generateLoginLinks(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Определяет, может ли пользователь генерировать ссылку для конкретного пользователя
     */
    public function generateLoginLinkFor(User $user, User $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            // Проверяем, является ли target студентом
            if (!$target->isStudent()) {
                return false;
            }

            // Проверяем, есть ли у учителя и студента общие команды
            $teacherTeamIds = $user->teams->pluck('id')->toArray();
            $studentTeamIds = $target->teams->pluck('id')->toArray();
            
            return !empty(array_intersect($teacherTeamIds, $studentTeamIds));
        }

        return false;
    }

    /**
     * Определяет, может ли пользователь просматривать список сгенерированных ссылок
     */
    public function viewLoginLinks(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Определяет, может ли пользователь просматривать личный кабинет учителя
     */
    public function viewTeacherLk(User $user): bool
    {
        return $user->isTeacher();
    }
}