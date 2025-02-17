<?php

namespace App\Filament\Resources\SettingGajiResource\Pages;

use App\Filament\Resources\SettingGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettingGajis extends ListRecords
{
    protected static string $resource = SettingGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
