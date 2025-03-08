<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Filament\Resources\AbsensiResource\RelationManagers;
use App\Models\Absensi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Components\BarcodeScanner;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\DateFilter;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Absensi Karyawan';

    public static function getModelLabel(): string
    {
        return 'Absensi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Absensi';
    }


    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Absensi';
    }
   

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BarcodeScanner::make('barcode')
                ->label('Scan Barcode'),
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('user.name')
                ->label('Nama Karyawan')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('shift.name')
                ->label('Shift')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('tanggal_absen')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('waktu_masuk_time'),
            Tables\Columns\TextColumn::make('waktu_keluar_time'),
            Tables\Columns\TextColumn::make('durasi_hadir')
                ->label('Durasi Hadir (Menit)')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('status_kehadiran')
                ->searchable(),
            Tables\Columns\TextColumn::make('keterangan')
                ->searchable(),
        ])
        ->filters([
            DateFilter::make('tanggal_absen')
                ->label('Filter Tanggal')
                ->placeholder('Pilih tanggal'),
            Filter::make('bulan')
                ->label('Filter Bulan')
                ->form([
                    Forms\Components\Select::make('bulan')
                        ->options([
                            '1' => 'Januari',
                            '2' => 'Februari',
                            '3' => 'Maret',
                            '4' => 'April',
                            '5' => 'Mei',
                            '6' => 'Juni',
                            '7' => 'Juli',
                            '8' => 'Agustus',
                            '9' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ])
                        ->placeholder('Pilih Bulan'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when($data['bulan'], function ($query, $bulan) {
                        return $query->whereMonth('tanggal_absen', $bulan);
                    });
                }),
            Filter::make('tahun')
                ->label('Filter Tahun')
                ->form([
                    Forms\Components\TextInput::make('tahun')
                        ->numeric()
                        ->placeholder('Masukkan Tahun'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when($data['tahun'], function ($query, $tahun) {
                        return $query->whereYear('tanggal_absen', $tahun);
                    });
                }),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
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
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
