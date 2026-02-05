<?php

use App\Models\Task;
use App\Models\Category;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public string $period = '30'; // days

    public function getHeatmapDataProperty(): array
    {
        $userId = auth()->id();
        $days = [];
        $startDate = now()->subDays(89); // ~3 months of data

        $tasks = Task::where('user_id', $userId)
            ->where('due_date', '>=', $startDate->toDateString())
            ->where('due_date', '<=', now()->toDateString())
            ->selectRaw('DATE(due_date) as date, COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            ->groupBy(DB::raw('DATE(due_date)'))
            ->get()
            ->keyBy('date');

        for ($i = 89; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->toDateString();
            $data = $tasks->get($dateStr);
            $total = $data?->total ?? 0;
            $completed = $data?->completed ?? 0;
            $percentage = $total > 0 ? round(($completed / $total) * 100) : -1; // -1 = no tasks

            $days[] = [
                'date' => $dateStr,
                'day' => $date->format('D'),
                'dayNum' => $date->format('j'),
                'month' => $date->format('M'),
                'total' => $total,
                'completed' => $completed,
                'percentage' => $percentage,
                'weekday' => $date->dayOfWeek,
            ];
        }

        return $days;
    }

    public function getCompletionTrendProperty(): array
    {
        $userId = auth()->id();
        $days = (int) $this->period;
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $total = Task::where('user_id', $userId)->whereDate('due_date', $date)->count();
            $completed = Task::where('user_id', $userId)->whereDate('due_date', $date)->where('is_completed', true)->count();

            $trend[] = [
                'date' => $date->format('M j'),
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }

        return $trend;
    }

    public function getPriorityDistributionProperty(): array
    {
        $userId = auth()->id();

        return [
            'high' => Task::where('user_id', $userId)->where('priority', 3)->count(),
            'medium' => Task::where('user_id', $userId)->where('priority', 2)->count(),
            'low' => Task::where('user_id', $userId)->where('priority', 1)->count(),
        ];
    }

    public function getCategoryBreakdownProperty()
    {
        return Category::where('user_id', auth()->id())
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
                $q->where('is_completed', true);
            }])
            ->orderByDesc('tasks_count')
            ->get();
    }

    public function getTagBreakdownProperty()
    {
        return Tag::where('user_id', auth()->id())
            ->withCount('tasks')
            ->orderByDesc('tasks_count')
            ->get();
    }

    public function getSummaryStatsProperty(): array
    {
        $userId = auth()->id();
        $days = (int) $this->period;
        $startDate = now()->subDays($days);

        $total = Task::where('user_id', $userId)->where('due_date', '>=', $startDate)->count();
        $completed = Task::where('user_id', $userId)->where('due_date', '>=', $startDate)->where('is_completed', true)->count();

        $totalAll = Task::where('user_id', $userId)->count();
        $completedAll = Task::where('user_id', $userId)->where('is_completed', true)->count();

        // Average tasks per day
        $activeDays = Task::where('user_id', $userId)
            ->where('due_date', '>=', $startDate)
            ->distinct('due_date')
            ->count('due_date');

        // Best day (most completions)
        $bestDay = Task::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('due_date', '>=', $startDate)
            ->selectRaw('DATE(due_date) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(due_date)'))
            ->orderByDesc('count')
            ->first();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'totalAll' => $totalAll,
            'completedAll' => $completedAll,
            'avgPerDay' => $activeDays > 0 ? round($total / $activeDays, 1) : 0,
            'bestDay' => $bestDay ? Carbon::parse($bestDay->date)->format('M j') : 'N/A',
            'bestDayCount' => $bestDay?->count ?? 0,
        ];
    }

    public function getWeekdayStatsProperty(): array
    {
        $userId = auth()->id();
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $stats = [];

        foreach ($dayNames as $i => $name) {
            $total = Task::where('user_id', $userId)
                ->whereRaw("strftime('%w', due_date) = ?", [$i])
                ->count();
            $completed = Task::where('user_id', $userId)
                ->whereRaw("strftime('%w', due_date) = ?", [$i])
                ->where('is_completed', true)
                ->count();

            $stats[] = [
                'day' => $name,
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }

        return $stats;
    }
}; ?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Statistics & Insights</h1>
            <p class="text-sm text-gray-500 mt-1">Track your productivity patterns and progress</p>
        </div>
        <select wire:model.live="period" class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 bg-white shadow-sm">
            <option value="7">Last 7 days</option>
            <option value="14">Last 14 days</option>
            <option value="30">Last 30 days</option>
            <option value="60">Last 60 days</option>
            <option value="90">Last 90 days</option>
        </select>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $this->summaryStats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Tasks</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $this->summaryStats['completed'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Completed</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $this->summaryStats['percentage'] }}%</p>
            <p class="text-xs text-gray-500 mt-1">Completion Rate</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $this->summaryStats['avgPerDay'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Avg Tasks/Day</p>
        </div>
    </div>

    {{-- Activity Heatmap --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Activity Heatmap</h2>
        <p class="text-sm text-gray-500 mb-4">Last 90 days of task completion activity</p>

        {{-- Legend --}}
        <div class="flex items-center gap-2 mb-4 text-xs text-gray-500">
            <span>Less</span>
            <div class="w-3 h-3 rounded-sm bg-gray-100"></div>
            <div class="w-3 h-3 rounded-sm bg-emerald-100"></div>
            <div class="w-3 h-3 rounded-sm bg-emerald-300"></div>
            <div class="w-3 h-3 rounded-sm bg-emerald-500"></div>
            <div class="w-3 h-3 rounded-sm bg-emerald-700"></div>
            <span>More</span>
        </div>

        {{-- Heatmap Grid --}}
        <div class="overflow-x-auto">
            <div class="flex gap-[3px] min-w-[600px]">
                @php
                    $weeks = collect($this->heatmapData)->chunk(7);
                @endphp
                @foreach($weeks as $week)
                    <div class="flex flex-col gap-[3px]">
                        @foreach($week as $day)
                            <div class="w-3.5 h-3.5 rounded-sm transition-colors cursor-pointer relative group
                                @if($day['percentage'] === -1) bg-gray-50
                                @elseif($day['percentage'] === 0) bg-red-100
                                @elseif($day['percentage'] < 25) bg-emerald-100
                                @elseif($day['percentage'] < 50) bg-emerald-200
                                @elseif($day['percentage'] < 75) bg-emerald-300
                                @elseif($day['percentage'] < 100) bg-emerald-500
                                @else bg-emerald-700
                                @endif
                            ">
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                                    <div class="bg-gray-800 text-white text-[10px] px-2 py-1 rounded-md whitespace-nowrap shadow-lg">
                                        {{ $day['month'] }} {{ $day['dayNum'] }}: {{ $day['completed'] }}/{{ $day['total'] }} tasks
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Completion Trend (CSS Bar Chart) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Completion Trend</h2>
            <div class="flex items-end gap-[2px] h-40 overflow-x-auto">
                @foreach($this->completionTrend as $day)
                    <div class="flex flex-col items-center gap-1 min-w-[8px] flex-1 group relative">
                        <div class="w-full rounded-t relative" style="height: {{ max($day['percentage'], 2) }}%; background: {{ $day['percentage'] >= 75 ? '#10b981' : ($day['percentage'] >= 50 ? '#f59e0b' : ($day['percentage'] > 0 ? '#ef4444' : '#e5e7eb')) }};">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block z-10">
                                <div class="bg-gray-800 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap">
                                    {{ $day['date'] }}: {{ $day['completed'] }}/{{ $day['total'] }} ({{ $day['percentage'] }}%)
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between mt-2 text-[10px] text-gray-400">
                <span>{{ $this->completionTrend[0]['date'] ?? '' }}</span>
                <span>{{ end($this->completionTrend)['date'] ?? '' }}</span>
            </div>
        </div>

        {{-- Priority Distribution --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Priority Distribution</h2>
            @php
                $priorityTotal = $this->priorityDistribution['high'] + $this->priorityDistribution['medium'] + $this->priorityDistribution['low'];
            @endphp
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-sm font-medium text-red-600">High Priority</span>
                        <span class="text-sm text-gray-500">{{ $this->priorityDistribution['high'] }} tasks ({{ $priorityTotal > 0 ? round(($this->priorityDistribution['high'] / $priorityTotal) * 100) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-red-400 to-red-500 rounded-full transition-all" style="width: {{ $priorityTotal > 0 ? round(($this->priorityDistribution['high'] / $priorityTotal) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-sm font-medium text-amber-600">Medium Priority</span>
                        <span class="text-sm text-gray-500">{{ $this->priorityDistribution['medium'] }} tasks ({{ $priorityTotal > 0 ? round(($this->priorityDistribution['medium'] / $priorityTotal) * 100) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all" style="width: {{ $priorityTotal > 0 ? round(($this->priorityDistribution['medium'] / $priorityTotal) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-sm font-medium text-emerald-600">Low Priority</span>
                        <span class="text-sm text-gray-500">{{ $this->priorityDistribution['low'] }} tasks ({{ $priorityTotal > 0 ? round(($this->priorityDistribution['low'] / $priorityTotal) * 100) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full transition-all" style="width: {{ $priorityTotal > 0 ? round(($this->priorityDistribution['low'] / $priorityTotal) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Breakdown --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Category Breakdown</h2>
            <div class="space-y-3">
                @foreach($this->categoryBreakdown as $category)
                    @php
                        $catPercentage = $category->tasks_count > 0 ? round(($category->completed_tasks_count / $category->tasks_count) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $category->color }}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 truncate">{{ $category->name }}</span>
                                <span class="text-xs text-gray-400 shrink-0 ml-2">{{ $category->completed_tasks_count }}/{{ $category->tasks_count }} ({{ $catPercentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width: {{ $catPercentage }}%; background-color: {{ $category->color }}"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Weekday Performance --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Weekday Performance</h2>
            <div class="flex items-end justify-between gap-2 h-40">
                @foreach($this->weekdayStats as $stat)
                    <div class="flex flex-col items-center gap-1 flex-1">
                        <span class="text-[10px] font-medium text-gray-500 mb-1">{{ $stat['percentage'] }}%</span>
                        <div class="w-full rounded-t-lg transition-all" style="height: {{ max($stat['percentage'], 3) }}%; background: linear-gradient(to top, #6366f1, #8b5cf6);"></div>
                        <span class="text-xs font-medium text-gray-600 mt-1">{{ $stat['day'] }}</span>
                        <span class="text-[10px] text-gray-400">{{ $stat['total'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tag Cloud --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tag Usage</h2>
            <div class="flex flex-wrap gap-3">
                @foreach($this->tagBreakdown as $tag)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-100 hover:shadow-sm transition"
                        style="background-color: {{ $tag->color }}08; border-color: {{ $tag->color }}30;">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $tag->color }}"></span>
                        <span class="text-sm font-medium" style="color: {{ $tag->color }}">{{ $tag->name }}</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">{{ $tag->tasks_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
