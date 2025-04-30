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
        return 'Beranda'; // Ganti dengan label yang kamu mau
    }

}
