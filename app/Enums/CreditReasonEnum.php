<?php

namespace App\Enums;

enum CreditReasonEnum: int
{
    case LESSON = 1;
    case ILLNESS = 2;
    case LESSON_ACHIEVEMENT = 3;
    case ADDITIONAL_ACHIEVEMENT = 4;
    case PAYMENT = 5;
    case MANUAL_ADJUSTMENT = 6;
    case LESSON_CANCELLED = 7;

    public function getName(): string
    {
        return match($this) {
            self::LESSON => 'Занятие',
            self::ILLNESS => 'Болезнь',
            self::LESSON_ACHIEVEMENT => 'Достижение на уроке',
            self::ADDITIONAL_ACHIEVEMENT => 'Дополнительное достижение',
            self::PAYMENT => 'Платеж',
            self::MANUAL_ADJUSTMENT => 'Ручная корректировка',
            self::LESSON_CANCELLED => 'Отмена занятия',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::LESSON => 'Посещение занятия',
            self::ILLNESS => 'Отсутствие по болезни',
            self::LESSON_ACHIEVEMENT => 'Достижения полученные во время занятия',
            self::ADDITIONAL_ACHIEVEMENT => 'Дополнительные достижения вне занятий',
            self::PAYMENT => 'Начисление за платеж',
            self::MANUAL_ADJUSTMENT => 'Ручная корректировка баланса',
            self::LESSON_CANCELLED => 'Возврат кредитов за отмененное занятие',
        };
    }
} 