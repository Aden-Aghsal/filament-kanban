@php
    $hexColor = match ($status['color'] ?? 'gray') {
        'success' => '#16a34a', // Hijau (Done)
        'danger' => '#dc2626',  // Merah (Revision)
        'warning' => '#ea580c', // Oranye (Check)
        'info' => '#2563eb',    // Biru (On Progress)
        default => '#6b7280',   // Abu-abu (Default)
    };
@endphp

<div 
    class="px-4 py-2 bg-white dark:bg-gray-900 rounded-t-lg shadow-sm mb-2"
    style="border-top: 4px solid {{ $hexColor }};"
>
    <h3 
        class="font-bold text-base flex justify-between items-center"
        style="color: {{ $hexColor }};"
    >
        <span>{{ $status['title'] }}</span>

<span 
    class="ml-2 px-2 py-0.5 text-xs rounded-full font-bold shadow-sm"
    style="
        background-color: white; 
        color: {{ $hexColor }};
        border: 1px solid {{ $hexColor }};
    "
>
    {{ count($status['records']) }}
</span>
    </h3>
</div>