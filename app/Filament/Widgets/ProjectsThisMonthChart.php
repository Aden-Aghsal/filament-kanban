<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ProjectsThisMonthChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Projects Created This Year';

    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // 2. Ambil data HANYA di tahun ini, lalu kelompokkan berdasarkan BULAN (Y-m)
        $projects = Project::whereYear('created_at', now()->year)
            ->get()
            ->groupBy(fn ($project) => $project->created_at->format('Y-m'));

        $labels = [];
        $data = [];
        
        // 3. Looping dari bulan 1 (Januari) sampai 12 (Desember)
        for ($i = 1; $i <= 12; $i++) {
            // Format pencocokan data: misal '2026-01', '2026-02', dst
            $dateString = now()->format('Y-') . str_pad($i, 2, '0', STR_PAD_LEFT);
            
            // Format teks di bawah grafik: Jan, Feb, Mar, dst
            $labels[] = Carbon::create()->month($i)->format('M');
            
            // Hitung jumlah project di bulan tersebut
            $data[] = isset($projects[$dateString]) ? $projects[$dateString]->count() : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Projects',
                    'data' => $data,
                    // Warna batang grafik (Teal / bawaan Filament)
                    'backgroundColor' => '#0d9488', 
                    'borderColor' => '#0d9488',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; 
    }
}