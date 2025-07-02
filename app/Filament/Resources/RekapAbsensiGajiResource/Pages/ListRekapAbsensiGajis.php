<?php

namespace App\Filament\Resources\RekapAbsensiGajiResource\Pages;

use App\Filament\Resources\RekapAbsensiGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekapAbsensiGajis extends ListRecords
{
    protected static string $resource = RekapAbsensiGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
