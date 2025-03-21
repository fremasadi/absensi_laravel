<?php

namespace App\Filament\Resources\BarcodeScannerPageResource\Pages;

use App\Filament\Resources\BarcodeScannerPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarcodeScannerPages extends ListRecords
{
    protected static string $resource = BarcodeScannerPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
