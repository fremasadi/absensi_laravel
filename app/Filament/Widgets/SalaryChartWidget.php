<?php

namespace App\Filament\Widgets;

use App\Models\Gaji;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

// class SalaryChartWidget extends ChartWidget
// {
//     protected static ?string $heading = 'Salary Distribution';
//     protected static ?int $sort = 3;
//     protected int | string | array $columnSpan = 'full';

//     protected function getData(): array
//     {
//         // Get the last 6 months
//         $months = collect(range(0, 5))->map(function ($month) {
//             $date = Carbon::now()->subMonths($month);
//             return [
//                 'label' => $date->format('M Y'),
//                 'month' => $date->month,
//                 'year' => $date->year,
//             ];
//         })->reverse()->values();

//         $salaryData = [];
//         $labels = [];

//         foreach ($months as $monthData) {
//             $labels[] = $monthData['label'];

//             $total = Gaji::whereMonth('periode_akhir', $monthData['month'])
//                 ->whereYear('periode_akhir', $monthData['year'])
//                 ->sum('total_gaji');

//             $salaryData[] = $total;
//         }

//         return [
//             'datasets' => [
//                 [
//                     'label' => 'Total Salary',
//                     'data' => $salaryData,
//                     'backgroundColor' => '#6366F1',
//                     'borderColor' => '#4F46E5',
//                     'tension' => 0.2,
//                 ],
//             ],
//             'labels' => $labels,
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'line';
//     }
// }