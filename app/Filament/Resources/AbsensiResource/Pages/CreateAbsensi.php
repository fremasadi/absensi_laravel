<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Pages\Actions\Action; // Import Action di sini
use Filament\Resources\Pages\CreateRecord;

class CreateAbsensi extends CreateRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getFormActions(): array
    {
        return [
            // Tombol Create tanpa teks, hanya ikon
            Action::make('create')
            ->hidden(),


            // Menyembunyikan tombol Cancel
            Action::make('cancel')
                ->hidden(),
        ];
    }
}