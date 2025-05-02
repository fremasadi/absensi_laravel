<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            
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
