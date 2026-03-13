<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            // Total User
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            // Total Project
            Stat::make('Total Projects', Project::count())
                ->description('All projects in the system')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('info'),

            // Total Task
            Stat::make('Total Tasks', Task::count())
                ->description('All tasks created')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('success'),
        ];
    }
}