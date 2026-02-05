<?php

use App\Models\Task;
use App\Models\Category;
use App\Models\Tag;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component
{
    public string $selectedDate = '';
    public string $filterStatus = 'all'; // all, completed, incomplete
    public ?int $filterCategory = null;
    public ?int $filterPriority = null;
    public string $searchQuery = '';

    // Task form
    public bool $showCreateForm = false;
    public bool $showEditForm = false;
    public ?int $editingTaskId = null;
    public string $taskTitle = '';
    public string $taskDescription = '';
    public int $taskPriority = 2;
    public ?int $taskCategoryId = null;
    public array $taskTagIds = [];
    public string $taskDueDate = '';

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->taskDueDate = $this->selectedDate;
    }

    public function updatedSelectedDate(): void
    {
        $this->taskDueDate = $this->selectedDate;
    }

    public function getCalendarDaysProperty(): array
    {
        $selected = Carbon::parse($this->selectedDate);
        $days = [];
        for ($i = -3; $i <= 3; $i++) {
            $date = $selected->copy()->addDays($i);
            $taskCount = Task::where('user_id', auth()->id())
                ->whereDate('due_date', $date)
                ->count();
            $completedCount = Task::where('user_id', auth()->id())
                ->whereDate('due_date', $date)
                ->where('is_completed', true)
                ->count();
            $days[] = [
                'date' => $date->toDateString(),
                'day' => $date->format('D'),
                'dayNum' => $date->format('d'),
                'month' => $date->format('M'),
                'isToday' => $date->isToday(),
                'isSelected' => $date->toDateString() === $this->selectedDate,
                'taskCount' => $taskCount,
                'completedCount' => $completedCount,
            ];
        }
        return $days;
    }

    public function getTasksProperty()
    {
        $query = Task::with(['category', 'tags'])
            ->where('user_id', auth()->id())
            ->whereDate('due_date', $this->selectedDate);

        if ($this->filterStatus === 'completed') {
            $query->where('is_completed', true);
        } elseif ($this->filterStatus === 'incomplete') {
            $query->where('is_completed', false);
        }

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->searchQuery) {
            $query->where('title', 'like', '%' . $this->searchQuery . '%');
        }

        return $query->orderBy('is_completed')->orderBy('priority', 'desc')->orderBy('sort_order')->get();
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', auth()->id())->orderBy('sort_order')->get();
    }

    public function getTagsProperty()
    {
        return Tag::where('user_id', auth()->id())->orderBy('name')->get();
    }

    public function getDayStatsProperty(): array
    {
        $total = Task::where('user_id', auth()->id())
            ->whereDate('due_date', $this->selectedDate)->count();
        $completed = Task::where('user_id', auth()->id())
            ->whereDate('due_date', $this->selectedDate)
            ->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function navigateDay(int $offset): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDays($offset)->toDateString();
        $this->taskDueDate = $this->selectedDate;
    }

    public function goToToday(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->taskDueDate = $this->selectedDate;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->taskDueDate = $date;
    }

    public function toggleTask(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);
        $task->update([
            'is_completed' => !$task->is_completed,
            'completed_at' => !$task->is_completed ? now() : null,
        ]);
    }

    public function openCreateForm(): void
    {
        $this->resetTaskForm();
        $this->taskDueDate = $this->selectedDate;
        $this->showCreateForm = true;
        $this->showEditForm = false;
    }

    public function openEditForm(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->with('tags')->findOrFail($taskId);
        $this->editingTaskId = $task->id;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description ?? '';
        $this->taskPriority = $task->priority;
        $this->taskCategoryId = $task->category_id;
        $this->taskTagIds = $task->tags->pluck('id')->toArray();
        $this->taskDueDate = $task->due_date->toDateString();
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function saveTask(): void
    {
        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDescription' => 'nullable|string|max:1000',
            'taskPriority' => 'required|in:1,2,3',
            'taskCategoryId' => 'nullable|exists:categories,id',
            'taskDueDate' => 'required|date',
        ]);

        $task = Task::create([
            'user_id' => auth()->id(),
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'priority' => $this->taskPriority,
            'category_id' => $this->taskCategoryId,
            'due_date' => $this->taskDueDate,
        ]);

        if (!empty($this->taskTagIds)) {
            $task->tags()->sync($this->taskTagIds);
        }

        $this->resetTaskForm();
        $this->showCreateForm = false;
    }

    public function updateTask(): void
    {
        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDescription' => 'nullable|string|max:1000',
            'taskPriority' => 'required|in:1,2,3',
            'taskCategoryId' => 'nullable|exists:categories,id',
            'taskDueDate' => 'required|date',
        ]);

        $task = Task::where('user_id', auth()->id())->findOrFail($this->editingTaskId);
        $task->update([
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'priority' => $this->taskPriority,
            'category_id' => $this->taskCategoryId,
            'due_date' => $this->taskDueDate,
        ]);

        $task->tags()->sync($this->taskTagIds);

        $this->resetTaskForm();
        $this->showEditForm = false;
    }

    public function deleteTask(int $taskId): void
    {
        Task::where('user_id', auth()->id())->findOrFail($taskId)->delete();
    }

    public function cancelForm(): void
    {
        $this->resetTaskForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    private function resetTaskForm(): void
    {
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskPriority = 2;
        $this->taskCategoryId = null;
        $this->taskTagIds = [];
        $this->editingTaskId = null;
        $this->taskDueDate = $this->selectedDate;
    }
}; ?>

<div class="space-y-4" x-data="{ showDatePicker: false }">
    {{-- Calendar Navigation Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Month/Year Header with Date Picker --}}
        <div class="px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
            <button wire:click="navigateDay(-7)" class="p-2 hover:bg-white/20 rounded-lg transition text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div class="flex items-center gap-3">
                <h2 class="text-white font-semibold text-lg">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('F Y') }}
                </h2>
                <div class="relative">
                    <input type="date" wire:model.live="selectedDate"
                        class="bg-white/20 text-white border-white/30 rounded-lg text-sm px-3 py-1.5 focus:ring-white/50 focus:border-white/50 cursor-pointer [color-scheme:dark]" />
                </div>
                @if($selectedDate !== now()->toDateString())
                    <button wire:click="goToToday" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition font-medium">
                        Today
                    </button>
                @endif
            </div>

            <button wire:click="navigateDay(7)" class="p-2 hover:bg-white/20 rounded-lg transition text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Day Selector Strip --}}
        <div class="flex items-center justify-between px-2 py-3 bg-gray-50/50">
            <button wire:click="navigateDay(-1)" class="p-2 hover:bg-gray-200 rounded-lg transition text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div class="flex gap-1 sm:gap-2 overflow-x-auto px-1">
                @foreach($this->calendarDays as $day)
                    <button wire:click="selectDate('{{ $day['date'] }}')"
                        class="flex flex-col items-center min-w-[4rem] sm:min-w-[5rem] py-2 px-2 sm:px-3 rounded-xl transition-all duration-200
                            {{ $day['isSelected'] ? 'bg-gradient-to-b from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-200 scale-105' : ($day['isToday'] ? 'bg-indigo-50 text-indigo-600 ring-2 ring-indigo-200' : 'hover:bg-gray-100 text-gray-600') }}">
                        <span class="text-[10px] font-medium uppercase tracking-wider {{ $day['isSelected'] ? 'text-indigo-100' : '' }}">{{ $day['day'] }}</span>
                        <span class="text-xl font-bold mt-0.5">{{ $day['dayNum'] }}</span>
                        <span class="text-[10px] {{ $day['isSelected'] ? 'text-indigo-100' : 'text-gray-400' }}">{{ $day['month'] }}</span>
                        @if($day['taskCount'] > 0)
                            <div class="flex items-center gap-0.5 mt-1">
                                <div class="w-1.5 h-1.5 rounded-full {{ $day['isSelected'] ? 'bg-white/80' : ($day['completedCount'] === $day['taskCount'] ? 'bg-emerald-400' : 'bg-indigo-400') }}"></div>
                                <span class="text-[9px] font-medium {{ $day['isSelected'] ? 'text-white/80' : 'text-gray-400' }}">{{ $day['completedCount'] }}/{{ $day['taskCount'] }}</span>
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>

            <button wire:click="navigateDay(1)" class="p-2 hover:bg-gray-200 rounded-lg transition text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-600">
                {{ \Carbon\Carbon::parse($selectedDate)->format('l, M j') }} Progress
            </span>
            <span class="text-sm font-bold {{ $this->dayStats['percentage'] === 100 ? 'text-emerald-600' : 'text-indigo-600' }}">
                {{ $this->dayStats['completed'] }}/{{ $this->dayStats['total'] }} tasks ({{ $this->dayStats['percentage'] }}%)
            </span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 ease-out {{ $this->dayStats['percentage'] === 100 ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}"
                 style="width: {{ $this->dayStats['percentage'] }}%"></div>
        </div>
    </div>

    {{-- Filters & Actions Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Search --}}
            <div class="flex-1 relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search tasks..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
            </div>

            {{-- Status Filter --}}
            <select wire:model.live="filterStatus" class="border border-gray-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 bg-white">
                <option value="all">All Tasks</option>
                <option value="incomplete">Incomplete</option>
                <option value="completed">Completed</option>
            </select>

            {{-- Category Filter --}}
            <select wire:model.live="filterCategory" class="border border-gray-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 bg-white">
                <option value="">All Categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Priority Filter --}}
            <select wire:model.live="filterPriority" class="border border-gray-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 bg-white">
                <option value="">All Priorities</option>
                <option value="3">High</option>
                <option value="2">Medium</option>
                <option value="1">Low</option>
            </select>

            {{-- Add Task Button --}}
            <button wire:click="openCreateForm"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-sm shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Task
            </button>
        </div>
    </div>

    {{-- Create/Edit Task Form --}}
    @if($showCreateForm || $showEditForm)
        <div class="bg-white rounded-2xl shadow-sm border border-indigo-200 p-6 ring-2 ring-indigo-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ $showEditForm ? 'Edit Task' : 'Create New Task' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" wire:model="taskTitle" placeholder="What needs to be done?"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
                    @error('taskTitle') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea wire:model="taskDescription" rows="2" placeholder="Add details..."
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select wire:model="taskPriority" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 bg-white">
                            <option value="1">Low</option>
                            <option value="2">Medium</option>
                            <option value="3">High</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select wire:model="taskCategoryId" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 bg-white">
                            <option value="">None</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" wire:model="taskDueDate"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" />
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->tags as $tag)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium cursor-pointer transition-all duration-200
                                {{ in_array($tag->id, $taskTagIds) ? 'ring-2 ring-offset-1' : 'opacity-60 hover:opacity-100' }}"
                                style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; {{ in_array($tag->id, $taskTagIds) ? 'ring-color: ' . $tag->color : '' }}">
                                <input type="checkbox" wire:model="taskTagIds" value="{{ $tag->id }}" class="hidden" />
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button wire:click="cancelForm" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition">
                        Cancel
                    </button>
                    @if($showEditForm)
                        <button wire:click="updateTask" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-sm">
                            Update Task
                        </button>
                    @else
                        <button wire:click="saveTask" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-sm">
                            Create Task
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Task List --}}
    <div class="space-y-2">
        @forelse($this->tasks as $task)
            <div wire:key="task-{{ $task->id }}"
                 class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md hover:border-gray-200 transition-all duration-200 {{ $task->is_completed ? 'opacity-75' : '' }}">
                <div class="flex items-start gap-3">
                    {{-- Checkbox --}}
                    <button wire:click="toggleTask({{ $task->id }})"
                        class="mt-0.5 w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200 shrink-0
                            {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 hover:border-indigo-400 hover:bg-indigo-50' }}">
                        @if($task->is_completed)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    {{-- Priority Indicator --}}
                    <div class="w-1.5 h-8 rounded-full shrink-0 mt-0.5
                        {{ $task->priority === 3 ? 'bg-red-400' : ($task->priority === 2 ? 'bg-amber-400' : 'bg-emerald-400') }}">
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-medium text-gray-800 {{ $task->is_completed ? 'line-through text-gray-400' : '' }}">
                                    {{ $task->title }}
                                </h4>
                                @if($task->description)
                                    <p class="text-sm text-gray-500 mt-0.5 line-clamp-1">{{ $task->description }}</p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition shrink-0">
                                <button wire:click="openEditForm({{ $task->id }})" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Are you sure you want to delete this task?"
                                    class="p-1.5 hover:bg-red-50 rounded-lg text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Meta: Category + Tags --}}
                        <div class="flex items-center flex-wrap gap-2 mt-2">
                            @if($task->category)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-lg"
                                    style="background-color: {{ $task->category->color }}15; color: {{ $task->category->color }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $task->category->color }}"></span>
                                    {{ $task->category->name }}
                                </span>
                            @endif
                            @foreach($task->tags as $tag)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                    style="background-color: {{ $tag->color }}15; color: {{ $tag->color }}">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $task->priority === 3 ? 'bg-red-50 text-red-600' : ($task->priority === 2 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                                {{ $task->priority_label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-gray-500 font-medium mb-1">No tasks for this day</h3>
                <p class="text-gray-400 text-sm mb-4">Start by adding a task to your schedule.</p>
                <button wire:click="openCreateForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add First Task
                </button>
            </div>
        @endforelse
    </div>
</div>
