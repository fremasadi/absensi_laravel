<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\Gaji;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms;

class LatestAttendanceWidget extends BaseTableWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Absensi::query()
                    ->with('user')
                    ->latest('tanggal_absen')
                    ->latest('waktu_masuk_time')
                    ->limit(5)
            )
            ->heading('Latest Attendance Records')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('tanggal_absen')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                    
                TextColumn::make('waktu_masuk_time')
                    ->label('Check In')
                    ->time('H:i')
                    ->sortable(),
                    
                TextColumn::make('waktu_keluar_time')
                    ->label('Check Out')
                    ->time('H:i')
                    ->sortable(),
                    
                TextColumn::make('durasi_hadir')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state . ' hours')
                    ->sortable(),
                    
                TextColumn::make('status_kehadiran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'tidak_hadir' => 'danger',
                        'terlambat' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_kehadiran')
                    ->options([
                        'hadir' => 'Present',
                        'tidak_hadir' => 'Absent',
                        'terlambat' => 'Late',
                    ]),
                    
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_absen', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_absen', '<=', $date),
                            );
                    }),
            ]);
    }
}

class PayrollWidget extends BaseTableWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Gaji::query()
                    ->with('user')
                    ->latest('periode_akhir')
                    ->limit(5)
            )
            ->heading('Recent Payroll Records')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('periode_awal')
                    ->label('Start Period')
                    ->date('d M Y')
                    ->sortable(),
                    
                TextColumn::make('periode_akhir')
                    ->label('End Period')
                    ->date('d M Y')
                    ->sortable(),
                    
                TextColumn::make('total_jam_kerja')
                    ->label('Work Hours')
                    ->formatStateUsing(fn ($state) => $state . ' hours')
                    ->sortable(),
                    
                TextColumn::make('total_gaji')
                    ->label('Total Salary')
                    ->money('IDR')
                    ->sortable(),
                    
                TextColumn::make('status_pembayaran')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sudah_dibayar' => 'success',
                        'belum_dibayar' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_pembayaran')
                    ->options([
                        'sudah_dibayar' => 'Paid',
                        'belum_dibayar' => 'Unpaid',
                        'pending' => 'Pending',
                    ]),
                    
                Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periode_awal', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periode_akhir', '<=', $date),
                            );
                    }),
            ]);
    }
}