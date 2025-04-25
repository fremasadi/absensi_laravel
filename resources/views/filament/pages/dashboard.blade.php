{{-- resources/views/filament/pages/dashboard.blade.php --}}
<x-filament::page>
    <x-slot name="head">
        {{-- Inject Chart.js di sini --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    {{-- Kalau kamu render widget manual --}}
    @livewire('filament.widgets.attendance-chart-widget')
</x-filament::page>
