<?php

namespace App\Providers\Filament;
use Filament\Navigation\NavigationGroup;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\BarcodeScannerPageResource;


class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        \Filament\Facades\Filament::registerNavigationGroups([
            \Filament\Navigation\NavigationGroup::make('Manajemen Absensi')
                ->icon(null), // Hapus ikon dari grup

            \Filament\Navigation\NavigationGroup::make('Manajemen Gaji')
                ->icon(null), // Hapus ikon dari grup

            \Filament\Navigation\NavigationGroup::make('Manajemen Shift')
                ->icon(null), // Hapus ikon dari grup

            \Filament\Navigation\NavigationGroup::make('Manajemen Pengguna')
                ->icon(null), // Hapus ikon dari grup
        ]);
    }
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \Filament\Pages\Dashboard::class, // Use default Filament dashboard
                ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\DashboardStats::class,      // Use App namespace
                \App\Filament\Widgets\AttendanceChartWidget::class,   // Use App namespace
                \App\Filament\Widgets\SalaryChartWidget::class,     // Use App namespace
                // \App\Filament\Widgets\LatestAttendanceWidget::class,  // Use App namespace
                // \App\Filament\Widgets\PayrollWidget::class,       // Use App namespace
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // EnsureAdmin::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->resources([
                BarcodeScannerPageResource::class, // Tambahkan resource ke navigasi
            ]);
    }
}
