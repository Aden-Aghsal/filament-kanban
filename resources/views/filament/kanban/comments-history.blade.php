<div class="space-y-3 max-h-60 overflow-y-auto p-2">
    @if (!empty($task->comments))
        @foreach ($task->comments as $comment)
            <div class="bg-white dark:bg-white/5 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">

                <div
                    class="flex justify-between items-center mb-1 border-b border-gray-100 dark:border-gray-700/50 pb-1">
                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
                        {{ $comment['user_name'] ?? 'Unknown' }}
                    </span>
                    <span class="text-[10px] text-gray-500 dark:text-gray-400">
                        {{ $comment['created_at'] ?? '' }}
                    </span>
                </div>

                <p class="text-sm text-gray-800 dark:text-gray-200 mt-1">
                    {{ $comment['content'] ?? '' }}
                </p>

            </div>
        @endforeach
    @else
        <p class="text-sm text-center text-gray-500 dark:text-gray-400 py-4">
            Belum ada komentar. Jadilah yang pertama memulai diskusi!
        </p>
    @endif
</div>
