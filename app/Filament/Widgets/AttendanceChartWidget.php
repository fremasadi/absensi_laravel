<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AttendanceChartWidget extends ApexChartWidget
{
    protected static ?string $heading = 'Attendance Overview';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $chartId = 'attendanceChart';
    
    protected function getOptions(): array
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
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Present',
                    'data' => $presentData,
                ],
                [
                    'name' => 'Absent',
                    'data' => $absentData,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
            ],
            'colors' => ['#10B981', '#EF4444'],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'endingShape' => 'rounded',
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'fill' => [
                'opacity' => 1,
            ],
        ];
    }
}