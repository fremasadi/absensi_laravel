<?php

namespace App\Filament\Resources\BarcodeScannerPageResource\Pages;

use App\Filament\Resources\BarcodeScannerPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;
use App\Filament\Components\BarcodeScanner;

class ManageBarcodeScannerPages extends Page
{
    protected static string $resource = BarcodeScannerPageResource::class;

    protected static string $view = 'filament.components.scan-qr-code'; // Sesuaikan dengan path view Anda

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