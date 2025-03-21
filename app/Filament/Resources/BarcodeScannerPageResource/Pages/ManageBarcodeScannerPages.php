<?php

namespace App\Filament\Resources\BarcodeScannerPageResource\Pages;

use App\Filament\Resources\BarcodeScannerPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;
use App\Filament\Components\BarcodeScanner;

class ManageBarcodeScannerPages extends Page
{
    protected static string $resource = BarcodeScannerPageResource::class;

    protected static string $view = 'filament.resources.barcode-scanner-page-resource.pages.manage-barcode-scanner-pages';

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