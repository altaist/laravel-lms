<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => 'regular',
            'description' => fake()->sentence(),
            'settings' => json_encode([
                'maxStudents' => 10,
                'isActive' => true
            ]),
            'json_schedule' => json_encode([
                'days' => ['monday', 'wednesday', 'friday'],
                'time' => '18:00'
            ]),
            'leader_id' => User::factory()
        ];
    }
} 