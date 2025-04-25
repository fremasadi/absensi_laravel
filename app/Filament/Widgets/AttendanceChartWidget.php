<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Jam Kehadiran Karyawan per Bulan';
    
    protected int | string | array $columnSpan = 'full';
    
    // Filter properties
    public ?string $selectedMonth = null;
    public ?int $selectedYear = null;
    public ?array $selectedUsers = null;
    
    public function mount(): void
    {
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
        $this->selectedUsers = User::pluck('id')->toArray(); // Default: select all users
    }
    
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('selectedDate')
                ->label('Pilih Bulan')
                ->displayFormat('F Y')
                ->default(now())
                ->afterStateUpdated(function ($state) {
                    $date = Carbon::parse($state);
                    $this->selectedMonth = $date->format('m');
                    $this->selectedYear = $date->format('Y');
                    $this->updateChartData();
                }),
                
            Select::make('selectedUsers')
                ->label('Pilih Karyawan')
                ->multiple()
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->afterStateUpdated(function () {
                    $this->updateChartData();
                }),
        ];
    }
    
    protected function getData(): array
    {
        // If no month/year selected, use current month
        if (!$this->selectedMonth || !$this->selectedYear) {
            $this->selectedMonth = now()->format('m');
            $this->selectedYear = now()->format('Y');
        }
        
        // If no users selected, show all users
        if (empty($this->selectedUsers)) {
            $this->selectedUsers = User::pluck('id')->toArray();
        }
        
        // Get start and end dates for the selected month
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();
        
        // Get all days in month for labels
        $period = $startDate->daysUntil($endDate);
        $labels = [];
        foreach ($period as $date) {
            $labels[] = $date->format('d M');
        }
        
        // Get attendance data for selected users and month
        $attendanceData = Absensi::select(
                'id_user',
                'tanggal_absen',
                'durasi_hadir'
            )
            ->whereIn('id_user', $this->selectedUsers)
            ->whereBetween('tanggal_absen', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Organize data by user and date
        $dataByUser = [];
        
        // Prepare data structure for each user
        foreach ($this->selectedUsers as $userId) {
            $userData = [];
            foreach ($period as $date) {
                $userData[$date->format('Y-m-d')] = 0;
            }
            $dataByUser[$userId] = $userData;
        }
        
        // Fill data from attendance records
        foreach ($attendanceData as $record) {
            $userId = $record->id_user;
            $date = $record->tanggal_absen;
            
            // Add hours for the day (durasi_hadir is stored in minutes, convert to hours)
            if (isset($dataByUser[$userId][$date])) {
                $dataByUser[$userId][$date] = $record->durasi_hadir / 60; // Convert to hours
            }
        }
        
        // Get user names for labels
        $userNames = User::whereIn('id', $this->selectedUsers)->pluck('name', 'id')->toArray();
        
        // Prepare chart datasets
        $datasets = [];
        $colors = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8AC249'];
        
        $colorIndex = 0;
        foreach ($dataByUser as $userId => $userData) {
            $color = $colors[$colorIndex % count($colors)];
            $colorIndex++;
            
            $datasets[] = [
                'label' => $userNames[$userId] ?? "User $userId",
                'data' => array_values($userData),
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        }
        
        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}