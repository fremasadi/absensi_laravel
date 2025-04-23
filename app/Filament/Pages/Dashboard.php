<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use App\Models\Gaji;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\ChartWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Closure;

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

class LatestAttendanceWidget extends Tables\Widgets\TableWidget
{
    protected static ?string $heading = 'Latest Attendance Records';
    protected static ?int $sort = 4;
    
    protected function getTableQuery(): Builder
    {
        return Absensi::query()
            ->with('user')
            ->latest('tanggal_absen')
            ->latest('waktu_masuk_time')
            ->limit(5);
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('user.name')
                ->label('Employee')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('tanggal_absen')
                ->label('Date')
                ->date('d M Y')
                ->sortable(),
                
            TextColumn::make('waktu_masuk_time')
                ->label('Check In')
                ->time('H:i')
                ->sortable(),
                
            TextColumn::make('waktu_keluar_time')
                ->label('Check Out')
                ->time('H:i')
                ->sortable(),
                
            TextColumn::make('durasi_hadir')
                ->label('Duration')
                ->formatStateUsing(fn ($state) => $state . ' hours')
                ->sortable(),
                
            BadgeColumn::make('status_kehadiran')
                ->label('Status')
                ->colors([
                    'success' => 'hadir',
                    'danger' => 'tidak_hadir',
                    'warning' => 'terlambat',
                ])
                ->searchable()
                ->sortable(),
        ];
    }
    
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status_kehadiran')
                ->options([
                    'hadir' => 'Present',
                    'tidak_hadir' => 'Absent',
                    'terlambat' => 'Late',
                ]),
                
            Filter::make('date')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from_date'),
                    \Filament\Forms\Components\DatePicker::make('to_date'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_absen', '>=', $date),
                        )
                        ->when(
                            $data['to_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_absen', '<=', $date),
                        );
                }),
        ];
    }
}

class PayrollWidget extends Tables\Widgets\TableWidget
{
    protected static ?string $heading = 'Recent Payroll Records';
    protected static ?int $sort = 5;
    
    protected function getTableQuery(): Builder
    {
        return Gaji::query()
            ->with('user')
            ->latest('periode_akhir')
            ->limit(5);
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('user.name')
                ->label('Employee')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('periode_awal')
                ->label('Start Period')
                ->date('d M Y')
                ->sortable(),
                
            TextColumn::make('periode_akhir')
                ->label('End Period')
                ->date('d M Y')
                ->sortable(),
                
            TextColumn::make('total_jam_kerja')
                ->label('Work Hours')
                ->formatStateUsing(fn ($state) => $state . ' hours')
                ->sortable(),
                
            TextColumn::make('total_gaji')
                ->label('Total Salary')
                ->money('IDR')
                ->sortable(),
                
            BadgeColumn::make('status_pembayaran')
                ->label('Payment Status')
                ->colors([
                    'success' => 'sudah_dibayar',
                    'danger' => 'belum_dibayar',
                    'warning' => 'pending',
                ])
                ->searchable()
                ->sortable(),
        ];
    }
    
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status_pembayaran')
                ->options([
                    'sudah_dibayar' => 'Paid',
                    'belum_dibayar' => 'Unpaid',
                    'pending' => 'Pending',
                ]),
                
            Filter::make('period')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from_date'),
                    \Filament\Forms\Components\DatePicker::make('to_date'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('periode_awal', '>=', $date),
                        )
                        ->when(
                            $data['to_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('periode_akhir', '<=', $date),
                        );
                }),
        ];
    }
}