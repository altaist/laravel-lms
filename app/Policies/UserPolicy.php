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
            return $target->isStudent() && $target->parent_id === $user->id;
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
}