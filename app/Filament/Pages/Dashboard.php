<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,         // 1. Statistik umum
            \App\Filament\Widgets\AttendanceChartWidget::class,  // 2. Grafik absensi
            \App\Filament\Widgets\SalaryChartWidget::class,      // 3. Grafik gaji
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Beranda';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home';
    }
}
