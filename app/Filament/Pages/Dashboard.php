<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use App\Models\Gaji;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\ChartWidget;
use App\Filament\Widgets\LatestAttendanceWidget;
use App\Filament\Widgets\PayrollWidget;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStats::class,
            AttendanceChartWidget::class,
            SalaryChartWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestAttendanceWidget::class,
            PayrollWidget::class,
        ];
    }
}

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
            Stat::make('Total Attendance Today', $totalAttendanceToday)
                ->description('Number of attendance records today')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            
            Stat::make('Present Users Today', $totalUsersPresent)
                ->description('Users marked as present today')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Total Salary This Month', 'Rp ' . number_format($totalSalaryThisMonth, 0, ',', '.'))
                ->description('Total salary for current month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('warning'),
                
            Stat::make('Average Work Hours', round($averageWorkHours, 1) . ' hours')
                ->description('Average work duration this month')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Attendance Overview';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $days = collect(range(1, 7))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->reverse()->toArray();
        
        $attendanceData = [];
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
    
    protected function getType(): string
    {
        return 'bar';
    }
}

class SalaryChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Salary Distribution';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        // Get the last 6 months
        $months = collect(range(0, 5))->map(function ($month) {
            $date = Carbon::now()->subMonths($month);
            return [
                'label' => $date->format('M Y'),
                'month' => $date->month,
                'year' => $date->year,
            ];
        })->reverse()->values();
        
        $salaryData = [];
        $labels = [];
        
        foreach ($months as $monthData) {
            $labels[] = $monthData['label'];
            
            $total = Gaji::whereMonth('periode_akhir', $monthData['month'])
                ->whereYear('periode_akhir', $monthData['year'])
                ->sum('total_gaji');
                
            $salaryData[] = $total;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Total Salary',
                    'data' => $salaryData,
                    'backgroundColor' => '#6366F1',
                    'borderColor' => '#4F46E5',
                    'tension' => 0.2,
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