<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AttendanceChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.attendance-chart-widget';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    
    public function getAttendanceData()
    {
        $days = collect(range(1, 7))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->reverse()->toArray();
        
        $data = [];
        
        foreach ($days as $day) {
            $date = Carbon::parse($day);
            $present = Absensi::whereDate('tanggal_absen', $day)
                ->where('status_kehadiran', 'hadir')
                ->count();
                
            $absent = Absensi::whereDate('tanggal_absen', $day)
                ->where('status_kehadiran', 'tidak_hadir')
                ->count();
                
            $data[] = [
                'date' => $date->format('D, d M'),
                'present' => $present,
                'absent' => $absent,
            ];
        }
        
        return $data;
    }
}