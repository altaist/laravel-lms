<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Database\Seeder;

class TestTeamsSeeder extends Seeder
{
    private array $teamTypes = [
        'regular' => 'Регулярные',
        'masterclass' => 'Мастер-классы',
        'new' => 'Новые'
    ];

    private function generateRandomTime(): array
    {
        $hours = [9, 10, 11, 12, 14, 15, 16, 17, 18, 19];
        $startHour = $hours[array_rand($hours)];
        
        // Длительность занятия 1-2 часа
        $duration = rand(1, 2);
        $endHour = $startHour + $duration;
        
        return [
            sprintf('%02d:00', $startHour),
            sprintf('%02d:00', $endHour)
        ];
    }

    private function generateRandomDays(): array
    {
        $availableDays = [1, 2, 3, 4, 5, 6, 7]; // Все дни недели
        $daysCount = rand(2, 4); // От 2 до 4 дней в неделю
        
        // Случайно выбираем дни
        shuffle($availableDays);
        $selectedDays = array_slice($availableDays, 0, $daysCount);
        sort($selectedDays); // Сортируем дни по порядку
        
        return $selectedDays;
    }

    public function run()
    {
        // Удаляем существующие данные
        Team::truncate();
        Schedule::truncate();
        ScheduleDay::truncate();

        // Создаем 10 пользователей
        $users = User::where('role_id', 10)->get();
        
        // Создаем группу "Новые ученики"
        $newStudentsTeam = Team::create([
            'name' => 'Новые ученики',
            'type' => 'new',
            'description' => 'Группа для новых учеников',
            'settings' => json_encode(['key' => 'value']),
            'json_schedule' => json_encode([
                'days' => [
                    [1, '10:00', '11:00'],  // Понедельник
                    [4, '10:00', '11:00']   // Четверг
                ]
            ]),
            'leader_id' => $users[0]->id
        ]);

        // Создаем расписание для группы новых учеников
        $schedule = Schedule::create(['team_id' => $newStudentsTeam->id]);
        foreach ([[1, '10:00', '11:00'], [4, '10:00', '11:00']] as $day) {
            ScheduleDay::create([
                'schedule_id' => $schedule->id,
                'day_of_week' => $day[0],
                'start_time' => $day[1],
                'end_time' => $day[2]
            ]);
        }

        // Создаем остальные группы
        $groupsToCreate = [
            ['regular', 5],    // 5 регулярных групп
            ['masterclass', 3] // 3 мастер-класса
        ];

        $teamNumber = 1;
        foreach ($groupsToCreate as [$type, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $days = $this->generateRandomDays();
                $scheduleDays = [];
                
                // Генерируем расписание для каждого выбранного дня
                foreach ($days as $day) {
                    [$startTime, $endTime] = $this->generateRandomTime();
                    $scheduleDays[] = [$day, $startTime, $endTime];
                }

                // Создаем команду
                $team = Team::create([
                    'name' => "{$this->teamTypes[$type]} #{$teamNumber}",
                    'type' => $type,
                    'description' => "Группа типа {$this->teamTypes[$type]}",
                    'settings' => json_encode(['key' => 'value']),
                    'json_schedule' => json_encode(['days' => $scheduleDays]),
                    'leader_id' => $users[$teamNumber % count($users)]->id
                ]);

                // Создаем расписание
                $schedule = Schedule::create(['team_id' => $team->id]);

                // Создаем дни расписания
                foreach ($scheduleDays as $day) {
                    ScheduleDay::create([
                        'schedule_id' => $schedule->id,
                        'day_of_week' => $day[0],
                        'start_time' => $day[1],
                        'end_time' => $day[2]
                    ]);
                }

                $teamNumber++;
            }
        }
        
        // Получаем все созданные команды
        $teams = Team::all();
        
        // Распределяем пользователей по командам
        foreach ($users as $user) {
            // Каждый пользователь будет состоять в 1-3 командах
            $teamCount = rand(1, 3);
            $randomTeams = $teams->random($teamCount);
            
            foreach ($randomTeams as $team) {
                $user->teams()->attach($team->id, [
                    'role_id' => Role::inRandomOrder()->first()->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
} 