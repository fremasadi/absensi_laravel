<?php

namespace App\Filament\Resources\SettingGajiResource\Pages;

use App\Filament\Resources\SettingGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;


class EditSettingGaji extends EditRecord
{
    protected static string $resource = SettingGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Jalankan perintah Artisan
        Artisan::call('salary:generate');

        // Tampilkan pesan sukses (opsional)
        $this->notify('success', 'Salary data generated successfully!');
    }
}
