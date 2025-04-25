<?php
// app/Filament/Widgets/AttendanceChartWidget.php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Absensi;
use Filament\Support\Assets\Js;

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Kehadiran';
    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = Absensi::query()
            ->selectRaw('MONTH(tanggal_absen) as month, COUNT(*) as count')
            ->whereYear('tanggal_absen', now()->year)
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        return [
            'labels' => $this->getMonthLabels($data->keys()),
            'datasets' => [
                [
                    'label' => 'Jumlah Absensi',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => '#3B82F6',
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => ['beginAtZero' => true]
            ]
        ];
    }

    private function getMonthLabels($months): array
    {
        $labels = [];
        foreach ($months as $month) {
            $labels[] = date('F', mktime(0, 0, 0, $month, 1));
        }
        return $labels;
    }

    public static function getAssets(): array
    {
        return [
            Js::make('chart-js', asset('js/chart.js'))->local(),
        ];
    }
}