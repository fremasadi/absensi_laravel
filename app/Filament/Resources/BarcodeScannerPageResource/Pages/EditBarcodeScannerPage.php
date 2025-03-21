<?php

namespace App\Filament\Resources\BarcodeScannerPageResource\Pages;

use App\Filament\Resources\BarcodeScannerPageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarcodeScannerPage extends EditRecord
{
    protected static string $resource = BarcodeScannerPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
