<?php

namespace App\Services;

use App\Models\User;

class StudentService
{
    public static function make(): self
    {
        return new self();
    }

    public function getStudentWithTeams(int $studentId): array
    {
        $student = User::with('teams')
            ->findOrFail($studentId);

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
} 