<?php

namespace App\Filament\Resources\JadwalShiftResource\Pages;

use App\Filament\Resources\JadwalShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJadwalShifts extends ListRecords
{
    protected static string $resource = JadwalShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
