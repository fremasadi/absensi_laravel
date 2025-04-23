<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,
            
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\AttendanceChartWidget::class,
            \App\Filament\Widgets\SalaryChartWidget::class,
        ];
    }
}