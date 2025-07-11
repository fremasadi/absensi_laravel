<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermintaanIzinResource\Pages;
use App\Filament\Resources\PermintaanIzinResource\RelationManagers;
use App\Models\PermintaanIzin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermintaanIzinResource extends Resource
{
    protected static ?string $model = PermintaanIzin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Cuti Karyawan';

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Izin Karyawan';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Absensi';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->required(),
                Forms\Components\Select::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->options([
                        'Sakit' => 'Sakit',
                        'Cuti' => 'Cuti',
                        'Keperluan Keluarga' => 'Keperluan Keluarga',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('alasan')
                    ->required()
                    ->columnSpanFull(),
                    Forms\Components\FileUpload::make('bukti_image')
                    ->label('Bukti Pendukung')
                    ->image()
                    ->directory('bukti-izin')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_izin')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                // Tables\Columns\IconColumn::make('status')
                //     ->boolean(),
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
            'index' => Pages\ListPermintaanIzins::route('/'),
            'create' => Pages\CreatePermintaanIzin::route('/create'),
            'edit' => Pages\EditPermintaanIzin::route('/{record}/edit'),
        ];
    }
}
