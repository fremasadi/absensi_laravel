<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PermintaanIzin;
use App\Observers\PermintaanIzinObserver;
use App\Models\SettingGaji;
use App\Observers\SettingGajiObserver;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsServiceProvider;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // PermintaanIzin::observe(PermintaanIzinObserver::class);
        SettingGaji::observe(SettingGajiObserver::class);
        $this->app->register(FilamentApexChartsServiceProvider::class);

        Filament::serving(function () {
            if (session()->has('error')) {
                Notification::make()
                    ->title('Login Gagal')
                    ->body(session('error'))
                    ->danger()
                    ->persistent()
                    ->send();
            }
        });
    
    }
}
