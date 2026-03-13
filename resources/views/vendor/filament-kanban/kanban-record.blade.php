@php
    $isMyTasks = (bool) ($record->is_my_tasks ?? false);

    if (! $isMyTasks) {
        $isMyTasks =
            str_contains((string) request()->header('referer'), 'my-tasks-board') ||
            str_contains(request()->path(), 'my-tasks-board');
    }

    $priorityValue = trim(
        strtolower($record->priority instanceof \BackedEnum ? $record->priority->value : (string) $record->priority),
    );

    $priorityColor = match ($priorityValue) {
        'high' => '#ef4444',
        'normal' => '#f59e0b',
        'low' => '#10b981',
        default => '#9ca3af',
    };

    // LOGIKA PENENTUAN SIAPA YANG DITAMPILKAN DI AVATAR BAWAH
    if ($isMyTasks) {
        // Di My Tasks, tampilkan Leader Project
        $targetUser = $record->project?->leader;
        $displayName = $targetUser?->name ?? 'No Leader';
    } else {
        // Di Project Board, tampilkan Assignee
        $targetUser = $record->assignee;
        $displayName = $targetUser?->name ?? 'Unassigned';
    }

    $userInitials = $targetUser
        ? collect(explode(' ', $displayName))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('')
        : '?';

    // Ambil nama depan saja biar nggak kepanjangan (opsional, kalau nama panjang ngerusak desain)
    $firstNameOnly = explode(' ', $displayName)[0];
@endphp

<div id="{{ $record->getKey() }}" wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record group flex flex-col p-3 cursor-grab rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 hover:border-primary-500/50 hover:shadow-md transition-all duration-200"
    style="width: 100%; height: 160px; min-height: 160px; max-height: 160px; overflow: hidden;"> {{-- PROJECT NAME --}}
    @if ($isMyTasks && $record->project)
        <div class="mb-1" style="width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <span class="text-gray-500 dark:text-gray-400 text-xs font-normal">
                {{ $record->project->name }}
            </span>
        </div>
    @endif

    {{-- TITLE --}}
    <div class="flex items-start gap-2 mb-2" style="width: 100%; overflow: hidden;">
        <div class="mt-[5px] w-3 h-3 rounded-full shrink-0" style="background-color: {{ $priorityColor }};"
            title="Priority: {{ ucfirst($priorityValue) }}">
        </div>

        <h4 title="{{ $record->title }}"
            class="font-normal text-gray-800 dark:text-gray-100 text-base leading-snug group-hover:text-primary-600"
            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-all; flex: 1; min-width: 0;">
            {{ $record->title }}
        </h4>
    </div>

    {{-- FOOTER --}}
    <div class="mt-auto pt-2 flex justify-between items-end border-t border-gray-100 dark:border-gray-800/50"
        style="width: 100%;">

        {{-- LEFT: Date & Comments --}}
        <div class="flex items-center gap-3 pb-1">

            @if ($record->due_date)
                @php
                    $dueDate = \Carbon\Carbon::parse($record->due_date)->endOfDay();
                    $isOverdue =
                        $dueDate &&
                        !($record->status === 'Done' || $record->status === \App\Enums\TaskStatus::Done) &&
                        $dueDate->isPast();
                @endphp

                <div class="flex items-center gap-1 text-sm shrink-0"
                    style="{{ $isOverdue ? 'color: #ef4444; font-weight: 600;' : 'color: #9ca3af;' }}">
                    <x-heroicon-o-calendar class="w-4 h-4" />
                    <span>{{ \Carbon\Carbon::parse($record->due_date)->format('d/m') }}</span>
                </div>
            @endif

            <div wire:click.stop="mountAction('commentTask', { record: '{{ $record->id }}' })"
                class="flex items-center gap-1 text-sm text-gray-400 shrink-0 cursor-pointer hover:text-teal-500 transition-colors">
                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4" />
                <span>{{ is_array($record->comments) ? count($record->comments) : 0 }}</span>
            </div>

        </div>

        {{-- RIGHT: Avatar + Name --}}
        <div class="flex flex-col items-center justify-center shrink-0 ml-2" title="{{ $displayName }}">

            @php $avatarUrl = $targetUser?->getFilamentAvatarUrl(); @endphp

            {{-- Avatar --}}
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" class="w-6 h-6 rounded-full object-cover shrink-0 mb-1 shadow-sm">
            @else
                <div
                    class="w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center shrink-0 text-[10px] font-bold text-white mb-1 shadow-sm">
                    {{ $userInitials }}
                </div>
            @endif

            {{-- Name under avatar --}}
            <span class="text-[9px] leading-none text-gray-500 dark:text-gray-400 text-center truncate max-w-[60px]">
                {{ $firstNameOnly }}
            </span>

        </div>
    </div>

</div>
