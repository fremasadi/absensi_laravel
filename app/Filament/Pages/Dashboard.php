<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,
            \App\Filament\Widgets\AttendanceChartWidget::class,
            \App\Filament\Widgets\SalaryChartWidget::class,
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
