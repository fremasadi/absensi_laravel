<?php

namespace App\Filament\Resources\RekapAbsensiGajiResource\Pages;

use App\Filament\Resources\RekapAbsensiGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapAbsensiGaji extends EditRecord
{
    protected static string $resource = RekapAbsensiGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
