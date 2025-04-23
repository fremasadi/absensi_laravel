<x-filament::page>
    {{-- Widget header --}}
    @if ($headerWidgets = $this->getHeaderWidgets())
        <x-filament::widgets
            :widgets="$headerWidgets"
            :columns="$this->getHeaderWidgetsColumns()"
        />
    @endif

    {{-- Konten utama --}}
    <div class="mt-6 space-y-6">
        {{-- Tabel --}}
        @foreach ($this->getTables() as $table)
            {{ $table }}
        @endforeach

        {{-- Widget footer --}}
        @if ($footerWidgets = $this->getFooterWidgets())
            <x-filament::widgets
                :widgets="$footerWidgets"
                :columns="$this->getFooterWidgetsColumns()"
            />
        @endif
    </div>
</x-filament::page>