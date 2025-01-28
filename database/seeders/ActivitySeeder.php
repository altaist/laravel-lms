<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        $teams = Team::all();
        
        foreach ($teams as $team) {
            // Генерируем от 1 до 3 активностей для каждой команды
            $activitiesCount = rand(1, 3);
            
            for ($i = 0; $i < $activitiesCount; $i++) {
                $startingAt = Carbon::now()->addDays(rand(1, 14))->setHour(rand(9, 18))->setMinute(0);
                
                Activity::create([
                    'team_id' => $team->id,
                    'name' => "Занятие {$team->name} #" . ($i + 1),
                    'description' => "Описание занятия {$team->name} #" . ($i + 1),
                    'json_content' => [
                        'topics' => ['Тема 1', 'Тема 2', 'Тема 3'],
                        'materials' => ['Материал 1', 'Материал 2']
                    ],
                    'json_results' => null,
                    'status' => Activity::STATUS_PENDING,
                    'starting_at' => $startingAt,
                    'started_at' => null,
                    'finished_at' => null,
                    'duration' => 60 // 60 минут
                ]);
            }
        }
    }
} 