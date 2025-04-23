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
            'labels' => $labels,
            'presentData' => $presentData,
            'absentData' => $absentData,
        ];
    }
    
    public function getChartId(): string
    {
        return 'attendance-chart-' . Str::random(8);
    }
}