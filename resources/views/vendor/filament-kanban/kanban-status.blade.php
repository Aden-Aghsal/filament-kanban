@props(['status'])

{{-- Paksa kunci lebar kolom di 230px pakai inline style --}}
<div class="flex-none mb-3 flex flex-col h-[70vh]" style="width: 230px; min-width: 230px; max-width: 230px;">
    @include(static::$headerView)

    <div data-status-id="{{ $status['id'] }}"
        class="flex flex-col gap-1.5 p-2 bg-gray-200 dark:bg-gray-800 rounded-lg flex-1 overflow-y-auto overflow-x-hidden">
        @foreach ($status['records'] as $record)
            @include(static::$recordView)
        @endforeach
    </div>
</div>
