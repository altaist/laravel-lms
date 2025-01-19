<?php

namespace Database\Seeders;

use App\Models\Reason;
use Illuminate\Database\Seeder;

class ReasonSeeder extends Seeder
{
    public function run()
    {
        $reasons = [
            [
                'id' => 1,
                'name' => 'Занятие',
                'description' => 'Посещение занятия'
            ],
            [
                'id' => 2,
                'name' => 'Болезнь',
                'description' => 'Отсутствие по болезни'
            ],
            [
                'id' => 3,
                'name' => 'Достижение на уроке',
                'description' => 'Достижения полученные во время занятия'
            ],
            [
                'id' => 4,
                'name' => 'Дополнительное достижение',
                'description' => 'Дополнительные достижения вне занятий'
            ],
        ];

        foreach ($reasons as $reason) {
            Reason::create($reason);
        }
    }
} 