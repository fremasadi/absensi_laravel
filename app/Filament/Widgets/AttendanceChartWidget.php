<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class AttendanceChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.attendance-chart-widget';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $chartId = null;
    
    public function getData(): array
    {
        $days = collect(range(1, 7))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->reverse()->toArray();
        
        $presentData = [];
        $absentData = [];
        $labels = [];
        
        foreach ($days as $day) {
            $date = Carbon::parse($day);
            $labels[] = $date->format('D, d M');
            
            $presentData[] = Absensi::whereDate('tanggal_absen', $day)
                ->where('status_kehadiran', 'hadir')
                ->count();
                
            $absentData[] = Absensi::whereDate('tanggal_absen', $day)
                ->where('status_kehadiran', 'tidak_hadir')
                ->count();
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $presentData,
                    'backgroundColor' => '#10B981',
                    'borderColor' => '#10B981',
                ],
                [
                    'label' => 'Absent',
                    'data' => $absentData,
                    'backgroundColor' => '#EF4444',
                    'borderColor' => '#EF4444',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    // Generate a unique ID for this widget instance
    public function getId(): string
    {
        if ($this->chartId === null) {
            $this->chartId = 'attendance-chart-' . Str::random(8);
        }
        
        return $this->chartId;
    }
}