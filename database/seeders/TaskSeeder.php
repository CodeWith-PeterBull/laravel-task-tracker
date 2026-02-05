<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function (User $user) {
            $categories = $user->categories;
            $tags = $user->tags;

            // Create tasks spread across the last 60 days and next 30 days
            for ($dayOffset = -60; $dayOffset <= 30; $dayOffset++) {
                $date = Carbon::today()->addDays($dayOffset);
                $taskCount = fake()->numberBetween(0, 6);

                for ($i = 0; $i < $taskCount; $i++) {
                    $isInPast = $dayOffset < 0;
                    $isCompleted = $isInPast ? fake()->boolean(75) : fake()->boolean(20);

                    $task = Task::create([
                        'user_id' => $user->id,
                        'category_id' => $categories->random()->id,
                        'title' => $this->generateTaskTitle(),
                        'description' => fake()->optional(0.6)->paragraph(),
                        'priority' => fake()->randomElement([1, 1, 2, 2, 2, 3]),
                        'due_date' => $date->toDateString(),
                        'is_completed' => $isCompleted,
                        'completed_at' => $isCompleted ? $date->copy()->addHours(fake()->numberBetween(8, 20)) : null,
                        'sort_order' => $i,
                    ]);

                    // Attach 1-3 random tags
                    $task->tags()->attach(
                        $tags->random(fake()->numberBetween(1, min(3, $tags->count())))->pluck('id')
                    );
                }
            }
        });
    }

    private function generateTaskTitle(): string
    {
        $titles = [
            'Review project documentation',
            'Update meeting notes',
            'Prepare presentation slides',
            'Send follow-up emails',
            'Code review for PR',
            'Fix bug in authentication',
            'Write unit tests',
            'Update database schema',
            'Design new landing page',
            'Optimize API endpoints',
            'Research new framework',
            'Refactor legacy code',
            'Set up CI/CD pipeline',
            'Write technical spec',
            'Plan sprint goals',
            'Schedule team standup',
            'Update project timeline',
            'Review pull requests',
            'Deploy to staging',
            'Run performance tests',
            'Document API changes',
            'Create user stories',
            'Organize workspace',
            'Read industry articles',
            'Exercise routine',
            'Meal prep planning',
            'Budget review',
            'Grocery shopping',
            'Clean home office',
            'Update personal website',
            'Practice meditation',
            'Study new language',
            'Backup important files',
            'Schedule dentist appointment',
            'Pay utility bills',
            'Plan weekend activities',
            'Review subscription costs',
            'Update resume',
            'Network with colleagues',
            'Attend webinar',
        ];

        return fake()->randomElement($titles);
    }
}
