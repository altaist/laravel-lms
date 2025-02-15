<?php

namespace App\Enums;

enum CoinEnum: int
{
    case RUBLE = 1;
    case LESSON = 2;
    case MINECRAFT = 3;
    case KNOWLEDGE = 4;
    case SKILL = 5;
    case DISCIPLINE = 6;
    case CREATIVE = 7;
    case FRIENDSHIP = 8;
    case CLASSROOM = 9;
    case HOME = 10;
    case EXTRA = 11;

    public function label(): string
    {
        return match($this) {
            self::RUBLE => 'Рубль',
            self::LESSON => 'Занятие',
            self::MINECRAFT => 'Майнкрафтик',
            self::KNOWLEDGE => 'Знание',
            self::SKILL => 'Умение',
            self::DISCIPLINE => 'Дисциплина',
            self::CREATIVE => 'Креатив',
            self::FRIENDSHIP => 'Дружба',
            self::CLASSROOM => 'Урок',
            self::HOME => 'Дом',
            self::EXTRA => 'Экстра',
        };
    }

    public function shortName(): string
    {
        return match($this) {
            self::RUBLE => 'р',
            self::LESSON => 'зан',
            self::MINECRAFT => 'майн',
            self::KNOWLEDGE => 'зн',
            self::SKILL => 'ум',
            self::DISCIPLINE => 'дисц',
            self::CREATIVE => 'кр',
            self::FRIENDSHIP => 'др',
            self::CLASSROOM => 'ур',
            self::HOME => 'дом',
            self::EXTRA => 'экс',
        };
    }

    public function code(): string
    {
        return match($this) {
            self::RUBLE => 'rub',
            self::LESSON => 'lesson',
            self::MINECRAFT => 'mine',
            self::KNOWLEDGE => 'knowledge',
            self::SKILL => 'skill',
            self::DISCIPLINE => 'discipline',
            self::CREATIVE => 'creative',
            self::FRIENDSHIP => 'friendship',
            self::CLASSROOM => 'classroom',
            self::HOME => 'home',
            self::EXTRA => 'extra',
        };
    }

    public function isVirtual(): bool
    {
        return match($this) {
            self::RUBLE => false,
            default => true,
        };
    }
} 