<?php

namespace App\Enums;

enum UserRoleEnum: int
{
    case ADMIN = 1;
    case MODERATOR = 2;
    case TEACHER = 3;
    case METHODIST = 4;
    case STUDENT = 10;
    case USER = 100;

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Администратор',
            self::MODERATOR => 'Модератор',
            self::TEACHER => 'Учитель',
            self::METHODIST => 'Методист',
            self::STUDENT => 'Ученик',
            self::USER => 'Пользователь',
        };
    }

    public function slug(): string
    {
        return match($this) {
            self::ADMIN => 'admin',
            self::MODERATOR => 'moderator',
            self::TEACHER => 'teacher',
            self::METHODIST => 'methodist',
            self::STUDENT => 'student',
            self::USER => 'user',
        };
    }
} 