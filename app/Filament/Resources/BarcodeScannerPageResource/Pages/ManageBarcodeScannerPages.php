<?php

namespace App\Filament\Resources\BarcodeScannerPageResource\Pages;

use App\Filament\Resources\BarcodeScannerPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;

class ManageBarcodeScannerPages extends Page
{
    protected static string $resource = BarcodeScannerPageResource::class;

    protected static string $view = 'filament.pages.barcode-scanner'; // Sesuaikan dengan path view Anda

    protected function getHeaderActions(): array
    {
        return [
            // Tidak perlu actions karena hanya menampilkan scanner
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            BarcodeScanner::make('barcode')
                ->label('Scan Barcode'),
        ];
    }
}