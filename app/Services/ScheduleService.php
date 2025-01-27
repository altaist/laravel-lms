<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function updateSchedule(Team $team, array $scheduleDays): array
    {
        try {
            DB::beginTransaction();

            // Получаем или создаем расписание для команды
            $schedule = Schedule::firstOrCreate(['team_id' => $team->id]);

            // Удаляем старые дни расписания
            $schedule->days()->delete();

            // Создаем новые дни расписания
            $newDays = collect($scheduleDays)->map(function ($day) use ($schedule) {
                return $schedule->days()->create([
                    'day_of_week' => $day['day_of_week'],
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time']
                ]);
            });

            // Обновляем JSON-расписание для обратной совместимости
            $team->update([
                'json_schedule' => [
                    'days' => $newDays->map(fn($day) => [
                        $day->day_of_week,
                        $day->start_time,
                        $day->end_time
                    ])->toArray()
                ]
            ]);

            DB::commit();

            return [
                'schedule_days' => $newDays,
                'json_schedule' => $team->json_schedule
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 