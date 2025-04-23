<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4">
            <h2 class="text-lg font-medium mb-4">Attendance Overview</h2>

            @php
                $data = $this->getData();
                $chartId = $this->getChartId();
                $labels = json_encode($data['labels']);
                $presentData = json_encode($data['presentData']);
                $absentData = json_encode($data['absentData']);
            @endphp

            {{-- Debug sementara --}}
            {{-- <pre>{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre> --}}

            <div class="w-full h-64">
                <canvas id="{{ $chartId }}" class="w-full h-full"></canvas>
            </div>

            @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {!! $labels !!},
                                datasets: [
                                    {
                                        label: 'Present',
                                        data: {!! $presentData !!},
                                        backgroundColor: '#10B981',
                                        borderColor: '#10B981'
                                    },
                                    {
                                        label: 'Absent',
                                        data: {!! $absentData !!},
                                        backgroundColor: '#EF4444',
                                        borderColor: '#EF4444'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endpush
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
