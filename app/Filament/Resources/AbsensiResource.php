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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Storage;
use App\Filament\Exports\ProductExporter;
use Filament\Tables\Actions\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Riwayat Absensi';

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
                Forms\Components\TextInput::make('id_user')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('id_jadwal')
                    ->numeric(),
                Forms\Components\DatePicker::make('tanggal_absen'),
                Forms\Components\TextInput::make('waktu_masuk_time'),
                Forms\Components\TextInput::make('waktu_keluar_time'),
                Forms\Components\TextInput::make('durasi_hadir')
                    ->numeric(),
                Forms\Components\TextInput::make('status_kehadiran')
                    ->maxLength(255),
                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('imageselfie')
                    ->maxLength(255),
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
                ->label('Durasi Hadir (Jam)')
                ->numeric()
                ->sortable()
                ->formatStateUsing(function ($state) {
                    // Ubah durasi dari menit ke jam dan bulatkan ke 2 desimal
                    return round($state / 60, 2);
                }),
                Tables\Columns\TextColumn::make('status_kehadiran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('selfiemasuk')
                    ->size(50, 50)
                    ->defaultImageUrl(asset('images/no_data.jpg'))
                    ->searchable(),

                Tables\Columns\ImageColumn::make('selfiekeluar')
                    ->size(50, 50)
                    ->defaultImageUrl(asset('images/no_data.jpg'))
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
            ->headerActions([
                ExportBulkAction::make()

            ])
            ->actions([
                Tables\Actions\Action::make('lihatSelfiemasuk')
                    ->label('Lihat Selfie Masuk')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Selfie Masuk')
                    ->modalContent(function ($record) {
                        if (!$record->selfiemasuk) {
                            return new \Illuminate\View\View('components.empty-state', [
                                'message' => 'Tidak ada data selfie masuk'
                            ]);
                        }

                        return view('components.image-viewer', [
                            'imageUrl' => Storage::url($record->selfiemasuk), // Konversi ke URL Storage
                            'alt' => 'Selfie Masuk'
                        ]);
                    })
                    ->modalFooterActions([
                        \Filament\Actions\Action::make('OK')
                            ->color('primary')
                            ->close(),

                        \Filament\Actions\Action::make('Buka di Tab Baru')
                            ->color('gray')
                            ->url(fn ($record) => Storage::url($record->selfiemasuk))
                            ->openUrlInNewTab()
                            ->hidden(fn ($record) => !$record->selfiemasuk),
                    ])
                    ->visible(fn ($record) => $record->selfiemasuk),
                Tables\Actions\Action::make('lihatSelfiekeluar')
                    ->label('Lihat Selfie Keluar')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Selfie Keluar')
                    ->modalContent(function ($record) {
                        if (!$record->selfiekeluar) {
                            return new \Illuminate\View\View('components.empty-state', [
                                'message' => 'Tidak ada data selfie keluar'
                            ]);
                        }

                        return view('components.image-viewer', [
                            'imageUrl' => Storage::url($record->selfiekeluar), // Konversi ke URL Storage
                            'alt' => 'Selfie Keluar'
                        ]);
                    })
                    ->modalFooterActions([
                        \Filament\Actions\Action::make('OK')
                            ->color('primary')
                            ->close(),

                        \Filament\Actions\Action::make('Buka di Tab Baru')
                            ->color('gray')
                            ->url(fn ($record) => Storage::url($record->selfiekeluar))
                            ->openUrlInNewTab()
                            ->hidden(fn ($record) => !$record->selfiekeluar),
                    ])
                    ->visible(fn ($record) => $record->selfiekeluar),            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()

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
            // 'create' => Pages\CreateAbsensi::route('/create'),
            // 'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
