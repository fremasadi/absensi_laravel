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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;

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
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // Filter tanggal kustom
            Filter::make('tanggal_absen')
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
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_absen', '>=', $date),
                        )
                        ->when(
                            $data['sampai_tanggal'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_absen', '<=', $date),
                        );
                }),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Action::make('export')
                    ->label('Unduh Excel')
                    ->action(fn () => Excel::download(new AbsensiExport, 'absensi.xlsx'))
                    ->icon('heroicon-o-download'),
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
