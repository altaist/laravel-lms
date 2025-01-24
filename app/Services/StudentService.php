<?php

namespace App\Services;

use App\Models\User;

class StudentService
{
    public static function make(): self
    {
        return new self();
    }

    public function getStudentWithTeams($studentId)
    {
        $student = User::with(['teams', 'activities', 'payments'])->findOrFail($studentId);
        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'teams' => $student->teams->map(fn($team) => [
                'id' => $team->id,
                'name' => $team->name
            ])
        ];
    }

    public function getPayments($studentId)
    {
        $student = User::findOrFail($studentId);
        // Предположим, что у студента есть связь с платежами
        return $student->payments; // Или любая другая логика получения платежей
    }

    public function getActivities($studentId)
    {
        $student = User::findOrFail($studentId);
        // Предположим, что у студента есть связь с занятиями
        return $student->activities; // Или любая другая логика получения занятий
    }
} 