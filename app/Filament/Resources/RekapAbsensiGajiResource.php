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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    return \Carbon\Carbon::createFromFormat('Y-m', $state)->format('F Y');
                })
                ->sortable(),
                
            Tables\Columns\TextColumn::make('format_periode')
                ->label('Tanggal')
                ->getStateUsing(function ($record) {
                    return \Carbon\Carbon::parse($record->periode_awal)->format('d/m/Y') . ' - ' . 
                           \Carbon\Carbon::parse($record->periode_akhir)->format('d/m/Y');
                }),
                
            Tables\Columns\TextColumn::make('total_hari_kerja')
                ->label('Total Hari')
                ->alignCenter()
                ->badge()
                ->color('gray'),
                
            Tables\Columns\TextColumn::make('total_hadir')
                ->label('Hadir')
                ->alignCenter()
                ->badge()
                ->color('success'),
                
            Tables\Columns\TextColumn::make('total_sakit')
                ->label('Sakit')
                ->alignCenter()
                ->badge()
                ->color('warning')
                ->visible(fn ($record) => $record->total_sakit > 0),
                
            Tables\Columns\TextColumn::make('total_izin')
                ->label('Izin')
                ->alignCenter()
                ->badge()
                ->color('info')
                ->visible(fn ($record) => $record->total_izin > 0),
                
            Tables\Columns\TextColumn::make('total_alpha')
                ->label('Alpha')
                ->alignCenter()
                ->badge()
                ->color('danger')
                ->visible(fn ($record) => $record->total_alpha > 0),
                
            Tables\Columns\TextColumn::make('persentase_kehadiran')
                ->label('Kehadiran')
                ->getStateUsing(function ($record) {
                    if ($record->total_hari_kerja == 0) return '0%';
                    return round(($record->total_hadir / $record->total_hari_kerja) * 100, 1) . '%';
                })
                ->badge()
                ->color(function ($state) {
                    $percentage = (float) str_replace('%', '', $state);
                    if ($percentage >= 90) return 'success';
                    if ($percentage >= 75) return 'warning';
                    return 'danger';
                }),
                
            Tables\Columns\TextColumn::make('total_jam_kerja')
                ->label('Total Jam')
                ->formatStateUsing(fn ($state) => number_format($state, 1) . ' jam')
                ->alignCenter(),
                
            Tables\Columns\TextColumn::make('gaji_per_jam')
                ->label('Gaji/Jam')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->alignRight(),
                
            Tables\Columns\TextColumn::make('total_gaji')
                ->label('Total Gaji')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
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
                        'approved' => 'Disetujui',
                        'paid' => 'Dibayar',
                        default => $state
                    };
                }),
                
            Tables\Columns\IconColumn::make('is_final')
                ->label('Final')
                ->boolean()
                ->alignCenter(),
                
            Tables\Columns\TextColumn::make('tanggal_rekap')
                ->label('Tgl Rekap')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
                
            Tables\Columns\TextColumn::make('creator.name')
                ->label('Dibuat Oleh')
                ->toggleable(isToggledHiddenByDefault: true),
                
            Tables\Columns\TextColumn::make('approver.name')
                ->label('Disetujui Oleh')
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
                        ->orderBy('bulan_tahun', 'desc')
                        ->pluck('bulan_tahun')
                        ->mapWithKeys(function ($item) {
                            return [$item => \Carbon\Carbon::createFromFormat('Y-m', $item)->format('F Y')];
                        });
                }),
                
            Tables\Filters\SelectFilter::make('status_rekap')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'approved' => 'Disetujui', 
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
            Tables\Actions\ViewAction::make()
                ->label('Lihat'),
                
            Tables\Actions\EditAction::make()
                ->label('Edit')
                ->visible(fn ($record) => $record->status_rekap === 'draft'),
                
            Tables\Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status_rekap === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Setujui Rekap Gaji')
                ->modalDescription('Apakah Anda yakin ingin menyetujui rekap gaji ini?')
                ->action(function ($record) {
                    $record->approve(auth()->id());
                    Notification::make()
                        ->title('Rekap berhasil disetujui')
                        ->success()
                        ->send();
                }),
                
            Tables\Actions\Action::make('mark_as_paid')
                ->label('Tandai Dibayar')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->visible(fn ($record) => $record->status_rekap === 'approved')
                ->requiresConfirmation()
                ->modalHeading('Tandai Sebagai Dibayar')
                ->modalDescription('Apakah gaji untuk rekap ini sudah dibayarkan?')
                ->action(function ($record) {
                    $record->markAsPaid();
                    Notification::make()
                        ->title('Rekap ditandai sebagai dibayar')
                        ->success()
                        ->send();
                }),
                
            Tables\Actions\Action::make('generate_slip')
                ->label('Slip Gaji')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->visible(fn ($record) => $record->status_rekap !== 'draft')
                ->url(fn ($record) => route('slip-gaji.show', $record->id))
                ->openUrlInNewTab(),
                
            Tables\Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->status_rekap === 'draft'),
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
                            if ($record->status_rekap === 'draft') {
                                $record->approve(auth()->id());
                                $count++;
                            }
                        }
                        Notification::make()
                            ->title("{$count} rekap berhasil disetujui")
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
