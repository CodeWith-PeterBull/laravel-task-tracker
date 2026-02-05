<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $isCompleted = fake()->boolean(40);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(fake()->numberBetween(3, 8)),
            'description' => fake()->optional(0.7)->paragraph(),
            'priority' => fake()->randomElement([1, 2, 3]),
            'due_date' => fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'sort_order' => fake()->numberBetween(0, 20),
            'is_recurring' => false,
            'recurrence_pattern' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function forToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->toDateString(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 3,
        ]);
    }
}
