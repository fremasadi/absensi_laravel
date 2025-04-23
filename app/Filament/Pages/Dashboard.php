<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use App\Models\Gajis;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class CustomDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.pages.custom-dashboard';
    
    protected static ?string $title = 'Employee Dashboard';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?int $navigationSort = -2;
    
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::make()
                ->stats([
                    Stat::make('Total Employees', Absensi::distinct('id_user')->count('id_user')),
                    Stat::make('Avg. Work Hours', round(Absensi::avg('durasi_hadir')/60, 1) . ' hrs'),
                    Stat::make('Pending Payments', Gajis::where('status_pembayaran', 'belum_dibayar')->count()),
                ]),
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            BarChartWidget::make('attendance_chart')
                ->label('Monthly Attendance')
                ->data([
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    'datasets' => [
                        [
                            'label' => 'Present',
                            'data' => $this->getMonthlyAttendanceData('hadir'),
                            'backgroundColor' => '#4CAF50',
                        ],
                        [
                            'label' => 'Absent',
                            'data' => $this->getMonthlyAttendanceData('tidak_hadir'),
                            'backgroundColor' => '#F44336',
                        ],
                    ],
                ]),
                
            LineChartWidget::make('salary_chart')
                ->label('Salary Distribution')
                ->data([
                    'labels' => ['< 2M', '2M-5M', '5M-10M', '> 10M'],
                    'datasets' => [
                        [
                            'label' => 'Employees',
                            'data' => $this->getSalaryDistributionData(),
                            'borderColor' => '#3B82F6',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        ],
                    ],
                ]),
        ];
    }
    
    protected function getMonthlyAttendanceData($status)
    {
        $data = Absensi::select(
                DB::raw('MONTH(tanggal_absen) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status_kehadiran', $status)
            ->whereYear('tanggal_absen', date('Y'))
            ->groupBy('month')
            ->get();
            
        $monthlyData = array_fill(0, 11, 0);
        
        foreach ($data as $item) {
            $monthlyData[$item->month - 1] = $item->count;
        }
        
        return $monthlyData;
    }
    
    protected function getSalaryDistributionData()
    {
        return [
            Gajis::where('total_gaji', '<', 2000000)->count(),
            Gajis::whereBetween('total_gaji', [2000000, 5000000])->count(),
            Gajis::whereBetween('total_gaji', [5000000, 10000000])->count(),
            Gajis::where('total_gaji', '>', 10000000)->count(),
        ];
    }
    
    protected function getTables(): array
    {
        return [
            TableWidget::make('recent_attendance')
                ->label('Recent Attendance')
                ->query(
                    Absensi::with(['user', 'jadwal'])
                        ->latest('tanggal_absen')
                        ->limit(5)
                )
                ->columns([
                    Tables\Columns\TextColumn::make('user.name')
                        ->label('Employee'),
                    Tables\Columns\TextColumn::make('tanggal_absen')
                        ->date(),
                    Tables\Columns\TextColumn::make('status_kehadiran')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'hadir' => 'success',
                            'tidak_hadir' => 'danger',
                            default => 'gray',
                        }),
                    Tables\Columns\TextColumn::make('durasi_hadir')
                        ->formatStateUsing(fn ($state) => floor($state/60) . 'h ' . ($state%60) . 'm'),
                ]),
                
            TableWidget::make('pending_payments')
                ->label('Pending Payments')
                ->query(
                    Gajis::with(['user', 'settingGaji'])
                        ->where('status_pembayaran', 'belum_dibayar')
                        ->latest('periode_akhir')
                        ->limit(5)
                )
                ->columns([
                    Tables\Columns\TextColumn::make('user.name')
                        ->label('Employee'),
                    Tables\Columns\TextColumn::make('periode_awal')
                        ->date(),
                    Tables\Columns\TextColumn::make('periode_akhir')
                        ->date(),
                    Tables\Columns\TextColumn::make('total_gaji')
                        ->money('IDR'),
                ]),
        ];
    }
}