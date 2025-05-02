<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalShiftResource\Pages;
use App\Filament\Resources\JadwalShiftResource\RelationManagers;
use App\Models\JadwalShift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JadwalShiftResource extends Resource
{
    protected static ?string $model = JadwalShift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Jadwal Karyawan';

    public static function getPluralModelLabel(): string
    {
        return 'Jadwal Karyawan';
    }


    public static function getNavigationGroup(): ?string
{
    return 'Manajemen Shift';
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_user')
                    ->label('User')
                    ->required()
                    ->relationship(
                        'user',
                        'name',
                        function ($query, $get) {
                            $query->where('role', 'user')
                                ->where(function ($subQuery) use ($get) {
                                    $subQuery->whereDoesntHave('jadwalShifts')
                                        ->orWhere('id', $get('id_user')); // tampilkan juga user yang sedang diedit
                                });
                        }
                    ),
                Forms\Components\Select::make('id_shift')
                ->label('Shift')
                ->required()
                ->relationship('shift', 'name'), // Mengambil relasi 'shift' dan menampilkan kolom 'name'
                Forms\Components\Toggle::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('user.name') // Menampilkan nama user dari relasi
                ->label('Nama')
                ->sortable(),
            Tables\Columns\TextColumn::make('shift.name') // Menampilkan nama shift dari relasi
                ->label('Shift')
                ->sortable(),
            Tables\Columns\IconColumn::make('status') // Menampilkan status dalam bentuk ikon
                ->boolean()
                ->label('Status'),
            Tables\Columns\TextColumn::make('created_at') // Menampilkan waktu dibuat
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at') // Menampilkan waktu diperbarui
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // Tambahkan filter jika diperlukan
        ])
        ->actions([
            Tables\Actions\EditAction::make(), // Menambahkan aksi edit
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(), // Menambahkan aksi hapus
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
            'index' => Pages\ListJadwalShifts::route('/'),
            'create' => Pages\CreateJadwalShift::route('/create'),
            'edit' => Pages\EditJadwalShift::route('/{record}/edit'),
        ];
    }
}
