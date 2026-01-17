<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Mengatur agar widget ini update otomatis setiap 15 detik (Realtime Monitoring)
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            // Kartu 1: Total Proyek Aktif
            Stat::make('Active Projects', Project::where('is_archived', false)->count())
                ->description('Proyek yang sedang berjalan')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            // Kartu 2: Task yang sedang dikerjakan
            Stat::make('On Progress', Task::where('status', TaskStatus::OnProgress)->count())
                ->description('Tugas sedang dikerjakan tim')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'), // Kuning biar eye-catching

            // Kartu 3: Task Selesai
            Stat::make('Completed Tasks', Task::where('status', TaskStatus::Done)->count())
                ->description('Tugas selesai bulan ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success') // Hijau
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Grafik mini hiasan
        ];
    }
}