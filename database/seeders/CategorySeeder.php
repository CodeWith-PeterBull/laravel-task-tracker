<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Work', 'color' => '#3b82f6', 'icon' => 'briefcase'],
            ['name' => 'Personal', 'color' => '#8b5cf6', 'icon' => 'user'],
            ['name' => 'Health', 'color' => '#10b981', 'icon' => 'heart'],
            ['name' => 'Learning', 'color' => '#f59e0b', 'icon' => 'book-open'],
            ['name' => 'Finance', 'color' => '#06b6d4', 'icon' => 'banknotes'],
            ['name' => 'Errands', 'color' => '#f97316', 'icon' => 'shopping-cart'],
        ];

        User::all()->each(function (User $user) use ($categories) {
            foreach ($categories as $i => $category) {
                Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $category['name']],
                    array_merge($category, ['user_id' => $user->id, 'sort_order' => $i])
                );
            }
        });
    }
}
