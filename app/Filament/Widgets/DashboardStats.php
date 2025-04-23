<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\Gaji;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Get today's date
        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Calculate statistics
        $totalAttendanceToday = Absensi::whereDate('tanggal_absen', $today)->count();
        $totalUsersPresent = Absensi::whereDate('tanggal_absen', $today)
            ->where('status_kehadiran', 'hadir')
            ->count();
        
        $totalSalaryThisMonth = Gaji::whereMonth('periode_akhir', $currentMonth)
            ->whereYear('periode_akhir', $currentYear)
            ->sum('total_gaji');
            
        $averageWorkHours = Absensi::whereMonth('tanggal_absen', $currentMonth)
            ->whereYear('tanggal_absen', $currentYear)
            ->avg('durasi_hadir');
        
        return [
            Stat::make('Jumlah Kehadiran Hari Ini', $totalAttendanceToday)
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            
            Stat::make('Pengguna Saat Ini', $totalUsersPresent)
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Total Gaji Bulan Ini', 'Rp ' . number_format($totalSalaryThisMonth, 0, ',', '.'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('warning'),
                
            Stat::make('Rata-rata Jam Kerja', round($averageWorkHours, 1) . ' jam')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}