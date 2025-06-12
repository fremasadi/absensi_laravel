<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// class EndanceChartWidget extends ChartWidget
// {
//     protected static ?string $heading = 'Total Jam Kehadiran Karyawan per Bulan';

//     protected int | string | array $columnSpan = 'full';

//     // Filter properties
//     public ?string $selectedMonth = null;
//     public ?int $selectedYear = null;
//     public ?array $selectedUsers = null;

//     protected static ?string $pollingInterval = null;

//     public function mount(): void
//     {
//         $this->selectedMonth = now()->format('m'); // default to current month
//         $this->selectedYear = now()->format('Y'); // default to current year
//         $this->selectedUsers = User::pluck('id')->toArray(); // Default: select all users
//     }

//     protected function getFormSchema(): array
//     {
//         return [
//             DatePicker::make('selectedDate')
//                 ->label('Pilih Bulan')
//                 ->displayFormat('F Y')
//                 ->default(now())
//                 ->afterStateUpdated(function ($state) {
//                     $date = Carbon::parse($state);
//                     $this->selectedMonth = $date->format('m');
//                     $this->selectedYear = $date->format('Y');
//                     $this->updateChartData();
//                 }),

//             Select::make('selectedUsers')
//                 ->label('Pilih Karyawan')
//                 ->multiple()
//                 ->options(User::pluck('name', 'id'))
//                 ->searchable()
//                 ->afterStateUpdated(function () {
//                     $this->updateChartData();
//                 }),
//         ];
//     }

//     protected function getData(): array
//     {
//         // If no month/year selected, use current month
//         if (!$this->selectedMonth || !$this->selectedYear) {
//             $this->selectedMonth = now()->format('m');
//             $this->selectedYear = now()->format('Y');
//         }

//         // If no users selected, show all users
//         if (empty($this->selectedUsers)) {
//             $this->selectedUsers = User::pluck('id')->toArray();
//         }

//         // Get start and end dates for the selected month
//         $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
//         $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

//         // Get month name for title
//         $monthName = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y');

//         // Get attendance data for selected users and month
//         $attendanceData = Absensi::selectRaw('id_user, SUM(durasi_hadir) as total_durasi')
//             ->whereIn('id_user', $this->selectedUsers)
//             ->whereBetween('tanggal_absen', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
//             ->groupBy('id_user')
//             ->get();

//         // Get user names for labels
//         $userNames = User::whereIn('id', $this->selectedUsers)->pluck('name', 'id')->toArray();

//         // Prepare data
//         $labels = [];
//         $data = [];

//         foreach ($attendanceData as $record) {
//             $userId = $record->id_user;
//             // Convert total_durasi from minutes to hours and round to 1 decimal place
//             $hoursPresent = round($record->total_durasi / 60, 1);

//             // Use user name as label
//             $labels[] = $userNames[$userId] ?? "User $userId";
//             $data[] = $hoursPresent;
//         }

//         // Color for the bars - single color for cleaner look
//         $backgroundColor = '#36A2EB';

//         return [
//             'datasets' => [
//                 [
//                     'label' => "Total Jam Kehadiran ($monthName)",
//                     'data' => $data,
//                     'backgroundColor' => $backgroundColor,
//                 ]
//             ],
//             'labels' => $labels,
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'bar'; // Diagram batang standar
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'responsive' => true,
//             'maintainAspectRatio' => false,
//             'scales' => [
//                 'y' => [
//                     'beginAtZero' => true,
//                     'title' => [
//                         'display' => true,
//                         'text' => 'Total Jam Kehadiran'
//                     ]
//                 ],
//                 'x' => [
//                     'title' => [
//                         'display' => true,
//                         'text' => 'Karyawan'
//                     ]
//                 ]
//             ],
//             'plugins' => [
//                 'legend' => [
//                     'display' => true,
//                     'position' => 'top',
//                 ],
//                 'tooltip' => [
//                     'callbacks' => [
//                         'label' => "function(context) { return context.raw + ' jam'; }"
//                     ]
//                 ]
//             ],
//         ];
//     }
// }