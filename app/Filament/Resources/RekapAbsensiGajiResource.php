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
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_awal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_akhir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_hari_kerja')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_jam_kerja')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_keterlambatan_menit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pulang_cepat_menit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tidak_hadir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_izin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gaji_per_jam')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_gaji_kotor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('potongan_keterlambatan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('potongan_tidak_hadir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_gaji_bersih')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pembayaran'),
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
                //
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListRekapAbsensiGajis::route('/'),
            'create' => Pages\CreateRekapAbsensiGaji::route('/create'),
            'edit' => Pages\EditRekapAbsensiGaji::route('/{record}/edit'),
        ];
    }
}
