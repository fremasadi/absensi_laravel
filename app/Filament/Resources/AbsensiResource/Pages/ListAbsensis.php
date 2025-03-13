<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\Action; // Ubah ini

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('export')
                ->label('Unduh Excel')
                ->icon('heroicon-o-arrow-down-tray') // Ubah ikon jika tidak ditemukan
                ->action(fn () => Excel::download(new AbsensiExport, 'absensi.xlsx'))
        ];
    }
}
