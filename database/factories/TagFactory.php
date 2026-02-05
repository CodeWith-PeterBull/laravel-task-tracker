<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->randomElement([
                'urgent', 'important', 'review', 'follow-up', 'waiting',
                'blocked', 'quick-win', 'deep-work', 'meeting', 'deadline',
            ]),
            'color' => fake()->randomElement([
                '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6',
                '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            ]),
        ];
    }
}
