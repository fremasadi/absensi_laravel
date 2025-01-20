<?php

namespace App\Filament\Resources\JadwalShiftResource\Pages;

use App\Filament\Resources\JadwalShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalShift extends EditRecord
{
    protected static string $resource = JadwalShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
