<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'json_content' => json_encode([
                'type' => 'lesson',
                'materials' => []
            ]),
            'json_results' => json_encode([]),
            'status' => Activity::STATUS_PENDING,
            'starting_at' => now(),
            'duration' => 60, // длительность в минутах
        ];
    }

    /**
     * Указать статус "Начато"
     */
    public function started(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Activity::STATUS_STARTED,
                'started_at' => now()
            ];
        });
    }

    /**
     * Указать статус "Завершено"
     */
    public function finished(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Activity::STATUS_FINISHED,
                'started_at' => now()->subHour(),
                'finished_at' => now()
            ];
        });
    }
} 