<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->randomElement([
                'Work', 'Personal', 'Health', 'Learning', 'Finance',
                'Shopping', 'Errands', 'Projects', 'Meetings', 'Goals',
            ]),
            'color' => fake()->randomElement([
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
                '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            ]),
            'icon' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
