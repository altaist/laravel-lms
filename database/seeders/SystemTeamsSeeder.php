<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Database\Seeder;

class SystemTeamsSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем системные команды
        Team::where('id', 1)->delete();
        
        // Создаем группу новичков
        $team = Team::create([
            'id' => 1,
            'name' => 'Группа новичков',
            'type' => 'new',
            'description' => 'Группа для новых учеников',
            'settings' => json_encode(['key' => 'value']),
            'json_schedule' => json_encode([
                'days' => [
                    [1, '10:00', '11:00'],  // Понедельник
                    [4, '10:00', '11:00']   // Четверг
                ]
            ]),
            'leader_id' => 1 // ID администратора
        ]);

        // Создаем расписание для группы
        $schedule = Schedule::create(['team_id' => $team->id]);

        // Добавляем дни расписания
        foreach ([[1, '10:00', '11:00'], [4, '10:00', '11:00']] as $day) {
            ScheduleDay::create([
                'schedule_id' => $schedule->id,
                'day_of_week' => $day[0],
                'start_time' => $day[1],
                'end_time' => $day[2]
            ]);
        }
    }
} 