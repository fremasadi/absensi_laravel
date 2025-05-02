<?php

namespace App\Filament\Pages\Dashboard;

use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            // \App\Filament\Widgets\DashboardStats::class,
            // \App\Filament\Widgets\AttendanceChartWidget::class,
            // \App\Filament\Widgets\SalaryChartWidget::class,
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
