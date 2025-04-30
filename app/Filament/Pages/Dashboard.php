<?php

// app/Filament/Pages/Dashboard.php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AttendanceChartWidget::class,
        ];
    }
    public static function getNavigationLabel(): string
{
    return 'Beranda'; // Ini label sidebar
}

public static function getNavigationIcon(): string
{
    return 'heroicon-o-home';
}

public static function getNavigationSort(): int
{
    return -1; // Supaya tampil paling atas
}


}
