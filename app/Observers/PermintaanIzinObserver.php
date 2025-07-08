<?php

namespace App\Observers;

use App\Models\PermintaanIzin;
use App\Models\Absensi;
use App\Models\JadwalShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PermintaanIzinObserver
{
    public function creating(PermintaanIzin $model)
    {
        \Log::info('Observer: Creating PermintaanIzin', [
            'attributes' => $model->getAttributes(),
            'image' => $model->image
        ]);
    }

    public function created(PermintaanIzin $model)
    {
        \Log::info('Observer: Created PermintaanIzin', [
            'id' => $model->id,
            'image' => $model->image
        ]);
    }

    public function updating(PermintaanIzin $model)
    {
        \Log::info('Observer: Updating PermintaanIzin', [
            'id' => $model->id,
            'changes' => $model->getDirty(),
            'image' => $model->image
        ]);
    }

    public function updated(PermintaanIzin $model)
    {
        \Log::info('Observer: Updated PermintaanIzin', [
            'id' => $model->id,
            'image' => $model->image
        ]);
    }
}
