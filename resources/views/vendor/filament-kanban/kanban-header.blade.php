@php
    $hexColor = match ($status['color'] ?? 'gray') {
        'success' => '#16a34a', // Hijau (Done)
        'danger' => '#dc2626', // Merah (Revision)
        'warning' => '#ea580c', // Oranye (Check)
        'info' => '#2563eb', // Biru (On Progress)
        default => '#6b7280', // Abu-abu (Default)
    };
@endphp

{{-- Saya ubah padding py-1 jadi py-2 supaya ruangnya lebih lega --}}
<div class="px-2.5 py-2 bg-white dark:bg-gray-900 rounded-t-lg shadow-sm mb-2"
    style="border-top: 4px solid {{ $hexColor }};">

    {{-- UBAH DI SINI: ganti text-xs menjadi text-base (atau text-lg kalau mau lebih besar lagi) --}}
    <h3 class="font-bold text-base flex justify-between items-center" style="color: {{ $hexColor }};">
        <span>{{ $status['title'] }}</span>

        {{-- UBAH DI SINI: Angka counter di dalam lingkaran juga saya besarkan sedikit dari text-[9px] jadi text-xs --}}
        <span class="ml-2 px-1.5 py-0.5 text-xs rounded-full font-bold shadow-sm"
            style="
        background-color: white; 
        color: {{ $hexColor }};
        border: 1px solid {{ $hexColor }};
    ">
            {{ count($status['records']) }}
        </span>
    </h3>
</div>
