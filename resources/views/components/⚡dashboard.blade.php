<?php

use App\Models\Task;
use App\Models\Category;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component
{
    public function getTodayStatsProperty(): array
    {
        $userId = auth()->id();
        $today = now()->toDateString();

        $total = Task::where('user_id', $userId)->whereDate('due_date', $today)->count();
        $completed = Task::where('user_id', $userId)->whereDate('due_date', $today)->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'remaining' => $total - $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function getWeekStatsProperty(): array
    {
        $userId = auth()->id();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $total = Task::where('user_id', $userId)->whereBetween('due_date', [$startOfWeek, $endOfWeek])->count();
        $completed = Task::where('user_id', $userId)->whereBetween('due_date', [$startOfWeek, $endOfWeek])->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function getOverallStatsProperty(): array
    {
        $userId = auth()->id();

        return [
            'total' => Task::where('user_id', $userId)->count(),
            'completed' => Task::where('user_id', $userId)->where('is_completed', true)->count(),
            'overdue' => Task::where('user_id', $userId)->where('is_completed', false)->where('due_date', '<', now()->toDateString())->count(),
            'highPriority' => Task::where('user_id', $userId)->where('is_completed', false)->where('priority', 3)->count(),
        ];
    }

    public function getCategoryStatsProperty()
    {
        return Category::where('user_id', auth()->id())
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
                $q->where('is_completed', true);
            }])
            ->orderBy('sort_order')
            ->get();
    }

    public function getUpcomingTasksProperty()
    {
        return Task::with(['category', 'tags'])
            ->where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('due_date', '>=', now()->toDateString())
            ->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();
    }

    public function getOverdueTasksProperty()
    {
        return Task::with(['category', 'tags'])
            ->where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date', 'desc')
            ->limit(5)
            ->get();
    }

    public function getStreakProperty(): int
    {
        $userId = auth()->id();
        $streak = 0;
        $date = now()->subDay();

        while (true) {
            $dayTasks = Task::where('user_id', $userId)->whereDate('due_date', $date)->count();
            if ($dayTasks === 0) {
                $date->subDay();
                if ($streak > 0 || $date->lt(now()->subDays(90))) break;
                continue;
            }
            $allCompleted = Task::where('user_id', $userId)
                ->whereDate('due_date', $date)
                ->where('is_completed', false)
                ->doesntExist();

            if ($allCompleted) {
                $streak++;
                $date->subDay();
            } else {
                break;
            }

            if ($streak > 365) break;
        }

        return $streak;
    }

    public function toggleTask(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);
        $task->update([
            'is_completed' => !$task->is_completed,
            'completed_at' => !$task->is_completed ? now() : null,
        ]);
    }
}; ?>

<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%2040%2040%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22%23fff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M20%200L40%2020L20%2040L0%2020z%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E')] opacity-50"></div>
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1">
                Welcome back, {{ auth()->user()->name }}!
            </h1>
            <p class="text-indigo-100 text-sm sm:text-base">{{ now()->format('l, F j, Y') }}</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-indigo-100 text-xs font-medium uppercase tracking-wider">Today</p>
                    <p class="text-3xl font-bold mt-1">{{ $this->todayStats['completed'] }}/{{ $this->todayStats['total'] }}</p>
                    <p class="text-indigo-200 text-xs mt-1">{{ $this->todayStats['percentage'] }}% complete</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-indigo-100 text-xs font-medium uppercase tracking-wider">This Week</p>
                    <p class="text-3xl font-bold mt-1">{{ $this->weekStats['completed'] }}/{{ $this->weekStats['total'] }}</p>
                    <p class="text-indigo-200 text-xs mt-1">{{ $this->weekStats['percentage'] }}% complete</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-indigo-100 text-xs font-medium uppercase tracking-wider">Streak</p>
                    <p class="text-3xl font-bold mt-1">{{ $this->streak }}</p>
                    <p class="text-indigo-200 text-xs mt-1">days in a row</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-indigo-100 text-xs font-medium uppercase tracking-wider">Overdue</p>
                    <p class="text-3xl font-bold mt-1 {{ $this->overallStats['overdue'] > 0 ? 'text-rose-300' : '' }}">{{ $this->overallStats['overdue'] }}</p>
                    <p class="text-indigo-200 text-xs mt-1">tasks behind</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Progress --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Today's Progress</h2>
            <a href="{{ route('tasks') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">View All</a>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden mb-2">
            <div class="h-full rounded-full transition-all duration-700 ease-out {{ $this->todayStats['percentage'] === 100 ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}"
                 style="width: {{ $this->todayStats['percentage'] }}%"></div>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">{{ $this->todayStats['completed'] }} completed</span>
            <span class="text-gray-500">{{ $this->todayStats['remaining'] }} remaining</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Overdue Tasks --}}
        @if($this->overdueTasks->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                <h2 class="text-lg font-semibold text-gray-800">Overdue Tasks</h2>
                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">{{ $this->overdueTasks->count() }}</span>
            </div>
            <div class="space-y-2">
                @foreach($this->overdueTasks as $task)
                    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-50/50 transition group">
                        <button wire:click="toggleTask({{ $task->id }})"
                            class="w-5 h-5 rounded border-2 border-gray-300 hover:border-red-400 hover:bg-red-50 flex items-center justify-center transition shrink-0">
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $task->title }}</p>
                            <p class="text-xs text-red-500">Due {{ $task->due_date->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $task->priority === 3 ? 'bg-red-50 text-red-600' : ($task->priority === 2 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                            {{ $task->priority_label }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Upcoming Tasks --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Upcoming Tasks</h2>
                <a href="{{ route('tasks') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">View All</a>
            </div>
            <div class="space-y-2">
                @forelse($this->upcomingTasks as $task)
                    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                        <button wire:click="toggleTask({{ $task->id }})"
                            class="w-5 h-5 rounded border-2 border-gray-300 hover:border-indigo-400 hover:bg-indigo-50 flex items-center justify-center transition shrink-0">
                        </button>
                        <div class="w-1 h-6 rounded-full shrink-0
                            {{ $task->priority === 3 ? 'bg-red-400' : ($task->priority === 2 ? 'bg-amber-400' : 'bg-emerald-400') }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $task->title }}</p>
                            <p class="text-xs text-gray-400">{{ $task->due_date->format('M j') }} &middot; {{ $task->category?->name ?? 'Uncategorized' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No upcoming tasks</p>
                @endforelse
            </div>
        </div>

        {{-- Categories Overview --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Categories</h2>
            <div class="space-y-3">
                @foreach($this->categoryStats as $category)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $category->color }}"></span>
                                <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $category->completed_tasks_count }}/{{ $category->tasks_count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                 style="width: {{ $category->tasks_count > 0 ? round(($category->completed_tasks_count / $category->tasks_count) * 100) : 0 }}%; background-color: {{ $category->color }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
