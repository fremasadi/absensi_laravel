<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingGajiResource\Pages;
use App\Filament\Resources\SettingGajiResource\RelationManagers;
use App\Models\SettingGaji;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingGajiResource extends Resource
{
    protected static ?string $model = SettingGaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Gaji Setting';

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Setting Gaji';
    }

    public static function getNavigationGroup(): ?string
    {
    return 'Manajemen Gaji';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('gaji_per_jam')
                    ->required()
                    ->numeric()
                    ->default(10000.00),
                Forms\Components\TextInput::make('periode_gaji')
                    ->required()
                    ->numeric()
                    ->default(14),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gaji_per_jam')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_gaji')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListSettingGajis::route('/'),
            'create' => Pages\CreateSettingGaji::route('/create'),
            'edit' => Pages\EditSettingGaji::route('/{record}/edit'),
        ];
    }
}
