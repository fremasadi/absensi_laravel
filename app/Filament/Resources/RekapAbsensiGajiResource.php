<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekapAbsensiGajiResource\Pages;
use App\Filament\Resources\RekapAbsensiGajiResource\RelationManagers;
use App\Models\RekapAbsensiGaji;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PDF;

class RekapAbsensiGajiResource extends Resource
{
    protected static ?string $model = RekapAbsensiGaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('bulan_tahun')
                    ->label('Periode')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        try {
                            return \Carbon\Carbon::createFromFormat('Y-m', $state)->format('F Y');
                        } catch (\Exception $e) {
                            return $state;
                        }
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('format_periode')
                    ->label('Tanggal')
                    ->getStateUsing(function ($record) {
                        if (!$record || !$record->periode_awal || !$record->periode_akhir) {
                            return '-';
                        }
                        try {
                            return \Carbon\Carbon::parse($record->periode_awal)->format('d/m/Y') . ' - ' . 
                                   \Carbon\Carbon::parse($record->periode_akhir)->format('d/m/Y');
                        } catch (\Exception $e) {
                            return '-';
                        }
                    }),
                    
                Tables\Columns\TextColumn::make('total_hari_kerja')
                    ->label('Total Hari')
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                    
                Tables\Columns\TextColumn::make('total_hadir')
                    ->label('Hadir')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                    
                Tables\Columns\TextColumn::make('total_sakit')
                    ->label('Sakit')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->visible(fn ($record) => $record && ($record->total_sakit ?? 0) > 0),
                    
                Tables\Columns\TextColumn::make('total_izin')
                    ->label('Izin')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->visible(fn ($record) => $record && ($record->total_izin ?? 0) > 0),
                    
                Tables\Columns\TextColumn::make('total_alpha')
                    ->label('Alpha')
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->visible(fn ($record) => $record && ($record->total_alpha ?? 0) > 0),
                    
                Tables\Columns\TextColumn::make('persentase_kehadiran')
                    ->label('Kehadiran')
                    ->getStateUsing(function ($record) {
                        if (!$record || ($record->total_hari_kerja ?? 0) == 0) return '0%';
                        $hadir = $record->total_hadir ?? 0;
                        $totalHari = $record->total_hari_kerja ?? 1;
                        return round(($hadir / $totalHari) * 100, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        $percentage = (float) str_replace('%', '', $state);
                        if ($percentage >= 90) return 'success';
                        if ($percentage >= 75) return 'warning';
                        return 'danger';
                    }),
                    
                Tables\Columns\TextColumn::make('total_jam_kerja')
                    ->label('Total Jam')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 1) . ' jam')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('gaji_per_jam')
                    ->label('Gaji/Jam')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->alignRight(),
                    
                Tables\Columns\TextColumn::make('total_gaji')
                    ->label('Total Gaji')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),
                    
                Tables\Columns\BadgeColumn::make('status_rekap')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'approved',
                        'primary' => 'paid',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'draft' => 'Draft',
                            'approved' => 'approved',
                            'paid' => 'Dibayar',
                            default => $state ?? 'Draft'
                        };
                    }),
                    
            
                    
                Tables\Columns\TextColumn::make('tanggal_rekap')
                    ->label('Tgl Rekap')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('approved Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Karyawan')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('bulan_tahun')
                    ->label('Bulan/Tahun')
                    ->options(function () {
                        return \App\Models\RekapAbsensiGaji::distinct()
                            ->whereNotNull('bulan_tahun')
                            ->orderBy('bulan_tahun', 'desc')
                            ->pluck('bulan_tahun')
                            ->filter() // Remove null values
                            ->mapWithKeys(function ($item) {
                                try {
                                    return [$item => \Carbon\Carbon::createFromFormat('Y-m', $item)->format('F Y')];
                                } catch (\Exception $e) {
                                    return [$item => $item];
                                }
                            });
                    }),
                    
                Tables\Filters\SelectFilter::make('status_rekap')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'approved', 
                        'paid' => 'Dibayar',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_final')
                    ->label('Status Final')
                    ->placeholder('Semua')
                    ->trueLabel('Final')
                    ->falseLabel('Belum Final'),
                    
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periode_awal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periode_akhir', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Action untuk Approve Rekap
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Rekap Gaji')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui rekap gaji ini?')
                    ->visible(fn (RekapAbsensiGaji $record) => $record->status_rekap !== 'approved')
                    ->action(function (RekapAbsensiGaji $record) {
                        $record->update([
                            'status_rekap' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'is_final' => true,
                        ]);
                        
                        Notification::make()
                            ->title('Rekap Berhasil approved')
                            ->success()
                            ->send();
                    }),
            
                // Action untuk Generate Slip Gaji (PDF)
                Tables\Actions\Action::make('slip_gaji')
                    ->label('Slip Gaji')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->visible(fn (RekapAbsensiGaji $record) => $record->status_rekap === 'approved')
                    ->action(function (RekapAbsensiGaji $record) {
                        // Generate PDF slip gaji
                        $pdf = PDF::loadView('slip-gaji.template', [
                            'rekap' => $record,
                            'user' => $record->user,
                            'settingGaji' => $record->settingGaji,
                        ]);
                        
                        $filename = "slip-gaji-{$record->user->name}-{$record->periode_awal->format('Y-m')}.pdf";
                        
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
            
                // Action untuk View Slip Gaji (Modal)
                Tables\Actions\Action::make('view_slip')
                    ->label('Lihat Slip')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->visible(fn (RekapAbsensiGaji $record) => $record->status_rekap === 'approved')
                    ->modalContent(function (RekapAbsensiGaji $record) {
                        return view('slip-gaji.modal', [
                            'rekap' => $record,
                            'user' => $record->user,
                            'settingGaji' => $record->settingGaji,
                        ]);
                    })
                    ->modalWidth('4xl')
                    ->modalHeading(fn (RekapAbsensiGaji $record) => 'Slip Gaji - ' . $record->user->name),
            
            
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete_rekap')),
                        
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Rekap Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui semua rekap yang dipilih?')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record && ($record->status_rekap ?? 'draft') === 'draft') {
                                    $record->approve(auth()->id());
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title("{$count} rekap berhasil approved")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('tanggal_rekap', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekapAbsensiGajis::route('/'),
        ];
    }
}