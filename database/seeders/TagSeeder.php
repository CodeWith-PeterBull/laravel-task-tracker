<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'urgent', 'color' => '#ef4444'],
            ['name' => 'important', 'color' => '#f59e0b'],
            ['name' => 'review', 'color' => '#3b82f6'],
            ['name' => 'follow-up', 'color' => '#8b5cf6'],
            ['name' => 'quick-win', 'color' => '#10b981'],
            ['name' => 'deep-work', 'color' => '#6366f1'],
            ['name' => 'meeting', 'color' => '#ec4899'],
            ['name' => 'deadline', 'color' => '#f97316'],
        ];

        User::all()->each(function (User $user) use ($tags) {
            foreach ($tags as $tag) {
                Tag::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $tag['name']],
                    array_merge($tag, ['user_id' => $user->id])
                );
            }
        });
    }
}
