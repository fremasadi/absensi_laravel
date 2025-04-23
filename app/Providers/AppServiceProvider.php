<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PermintaanIzin;
use App\Observers\PermintaanIzinObserver;
use App\Models\SettingGaji;
use App\Observers\SettingGajiObserver;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->register(FilamentApexChartsServiceProvider::class);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        PermintaanIzin::observe(PermintaanIzinObserver::class);
        SettingGaji::observe(SettingGajiObserver::class);


    }
}
