<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarcodeScannerPageResource\Pages;
use App\Filament\Resources\BarcodeScannerPageResource\RelationManagers;
use App\Models\BarcodeScannerPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarcodeScannerPageResource extends Resource
{
    protected static ?string $model = null; // Tidak perlu model karena tidak ada data yang disimpan

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationLabel = 'Scan Barcode'; // Label navigasi

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Absensi';
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
           
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
            'index' => Pages\ManageBarcodeScannerPages::route('/'),
            
        ];
    }
}
