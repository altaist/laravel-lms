<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Получаем все команды с расписанием
        $teams = DB::table('teams')
            ->whereNotNull('json_schedule')
            ->get();

        foreach ($teams as $team) {
            $schedule = json_decode($team->json_schedule);
            
            if (isset($schedule->days)) {
                // Создаем новое расписание
                $scheduleId = DB::table('schedules')->insertGetId([
                    'team_id' => $team->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Добавляем дни расписания
                foreach ($schedule->days as $day) {
                    DB::table('schedule_days')->insert([
                        'schedule_id' => $scheduleId,
                        'day_of_week' => $day[0],
                        'start_time' => $day[1],
                        'end_time' => $day[2],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Очищаем новые таблицы
        DB::table('schedule_days')->truncate();
        DB::table('schedules')->truncate();
    }
}; 