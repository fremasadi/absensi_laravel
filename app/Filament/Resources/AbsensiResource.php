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

// class AbsensiResource extends Resource
// {
//     protected static ?string $model = Absensi::class;

//     protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

//     public static function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\TextInput::make('id_pengguna')
//                     ->required()
//                     ->numeric(),
//                 Forms\Components\TextInput::make('id_jadwal')
//                     ->numeric(),
//                 Forms\Components\DateTimePicker::make('waktu_masuk'),
//                 Forms\Components\DateTimePicker::make('waktu_keluar'),
//                 Forms\Components\TextInput::make('durasi_terlambat')
//                     ->numeric(),
//                 Forms\Components\TextInput::make('status_kehadiran')
//                     ->maxLength(255),
//                 Forms\Components\TextInput::make('keterangan')
//                     ->maxLength(255),
//             ]);
//     }

//     public static function table(Table $table): Table
//     {
//         return $table
//             ->columns([
//                 Tables\Columns\TextColumn::make('id_pengguna')
//                     ->numeric()
//                     ->sortable(),
//                 Tables\Columns\TextColumn::make('id_jadwal')
//                     ->numeric()
//                     ->sortable(),
//                 Tables\Columns\TextColumn::make('waktu_masuk')
//                     ->dateTime()
//                     ->sortable(),
//                 Tables\Columns\TextColumn::make('waktu_keluar')
//                     ->dateTime()
//                     ->sortable(),
//                 Tables\Columns\TextColumn::make('durasi_terlambat')
//                     ->numeric()
//                     ->sortable(),
//                 Tables\Columns\TextColumn::make('status_kehadiran')
//                     ->searchable(),
//                 Tables\Columns\TextColumn::make('keterangan')
//                     ->searchable(),
//                 Tables\Columns\TextColumn::make('created_at')
//                     ->dateTime()
//                     ->sortable()
//                     ->toggleable(isToggledHiddenByDefault: true),
//                 Tables\Columns\TextColumn::make('updated_at')
//                     ->dateTime()
//                     ->sortable()
//                     ->toggleable(isToggledHiddenByDefault: true),
//             ])
//             ->filters([
//                 //
//             ])
//             ->actions([
//                 Tables\Actions\EditAction::make(),
//             ])
//             ->bulkActions([
//                 Tables\Actions\BulkActionGroup::make([
//                     Tables\Actions\DeleteBulkAction::make(),
//                 ]),
//             ]);
//     }

//     public static function getRelations(): array
//     {
//         return [
//             //
//         ];
//     }

//     public static function getPages(): array
//     {
//         return [
//             'index' => Pages\ListAbsensis::route('/'),
//             'create' => Pages\CreateAbsensi::route('/create'),
//             'edit' => Pages\EditAbsensi::route('/{record}/edit'),
//         ];
//     }
// }
