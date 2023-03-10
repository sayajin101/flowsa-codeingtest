<?php

namespace Database\Factories;

use Carbon\Carbon;
use \App\Enum\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(1),
            'status' => fake()->randomElement(Status::cases()),
            'priority' => random_int(0, 4),
            'deadline' => fake()->boolean() ? (new Carbon())->addMinutes(random_int(-1000, 2500)) : null
        ];
    }
}
