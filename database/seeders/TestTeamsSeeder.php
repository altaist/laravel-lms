<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class TestTeamsSeeder extends Seeder
{
    public function run()
    {
        // Создаем 10 пользователей
        $users = User::factory(10)->create();
        
        // Создаем 5 команд с разными типами
        $teamTypes = ['development', 'marketing', 'sales', 'support', 'management'];
        
        foreach ($teamTypes as $index => $type) {
            $leader = $users[$index]; // Первые 5 пользователей станут лидерами команд
            
            Team::create([
                'name' => ucfirst($type) . ' Team',
                'type' => $type,
                'description' => "This is {$type} team description",
                'settings' => json_encode(['key' => 'value']),
                'schedule' => json_encode([
                    'workdays' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                    'hours' => '9:00-18:00'
                ]),
                'leader_id' => $leader->id
            ]);
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
                    'role_id' => Role::inRandomOrder()->first()->id, // Предполагается, что роли уже существуют
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
} 