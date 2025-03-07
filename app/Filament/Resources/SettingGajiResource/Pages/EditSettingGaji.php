<?php

namespace App\Filament\Resources\SettingGajiResource\Pages;

use App\Filament\Resources\SettingGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

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
        Notification::make()
        ->title('Setting Gaji Berhasil Diubah')
        ->success()
        ->send();    }
}
