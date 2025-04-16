<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajiResource\Pages;
use App\Filament\Resources\GajiResource\RelationManagers;
use App\Models\Gaji;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Placeholder;  // Ganti StaticText dengan Placeholder

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Gaji Karyawan';

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Gaji';
    }

    public static function getNavigationGroup(): ?string
    {
    return 'Manajemen Gaji';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Data yang hanya ditampilkan (read-only)
                Placeholder::make('user_name')
                    ->label('Nama Karyawan')
                    ->content(fn ($record) => $record->user->name),

                Placeholder::make('periode_awal')
                    ->label('Periode Awal')
                    ->content(fn ($record) => $record->periode_awal->format('d/m/Y')),

                Placeholder::make('periode_akhir')
                    ->label('Periode Akhir')
                    ->content(fn ($record) => $record->periode_akhir->format('d/m/Y')),

                Placeholder::make('total_jam_kerja')
                    ->label('Total Jam Kerja')
                    ->content(fn ($record) => $record->total_jam_kerja),

                Placeholder::make('gaji_per_jam')
                    ->label('Gaji Per Jam')
                    ->content(fn ($record) => 'Rp ' . number_format($record->gaji_per_jam, 0, ',', '.')),

                Placeholder::make('total_gaji')
                    ->label('Total Gaji')
                    ->content(fn ($record) => 'Rp ' . number_format($record->total_gaji, 0, ',', '.')),

                // Form yang bisa diubah
                Forms\Components\Select::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'belum_dibayar' => 'Belum Dibayar',
                        'sudah_dibayar' => 'Sudah Dibayar'
                    ])
                    ->required()
                    ->default('belum_dibayar'),

                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('settingGaji.name')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('periode_awal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_akhir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_jam_kerja')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_gaji')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('unduh_slip')
                    ->label('Slip Gaji')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => route('gaji.downloadSlipGaji', $record->id))
                    ->openUrlInNewTab()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'); // Urutkan secara descending berdasarkan `created_at`

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
            'index' => Pages\ListGajis::route('/'),
            'create' => Pages\CreateGaji::route('/create'),
            'edit' => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}
