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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

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
                // Filter berdasarkan tanggal
                Filter::make('tanggal_absen')
                    ->form([
                        DatePicker::make('tanggal_absen')
                            ->label('Pilih Tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query->when($data['tanggal_absen'] ?? null, fn ($query, $tanggal) => 
                            $query->whereDate('tanggal_absen', $tanggal)
                        )
                    ),
    
                // Filter berdasarkan bulan
                Filter::make('bulan')
                    ->form([
                        Select::make('bulan')
                            ->label('Pilih Bulan')
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
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query->when($data['bulan'] ?? null, fn ($query, $bulan) => 
                            $query->whereMonth('tanggal_absen', $bulan)
                        )
                    ),
    
                // Filter berdasarkan tahun
                Filter::make('tahun')
                    ->form([
                        TextInput::make('tahun')
                            ->label('Masukkan Tahun')
                            ->numeric()
                            ->placeholder('Contoh: 2024'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query->when($data['tahun'] ?? null, fn ($query, $tahun) => 
                            $query->whereYear('tanggal_absen', $tahun)
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
