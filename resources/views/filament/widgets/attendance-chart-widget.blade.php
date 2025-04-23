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
            
            <div class="w-full h-64">
                <canvas id="{{ $chartId }}" class="w-full h-full"></canvas>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Create a simple function to load Chart.js and initialize
                    var initializeChart = function() {
                        var chartId = '{{ $chartId }}';
                        var chartElement = document.getElementById(chartId);
                        
                        if (!chartElement) {
                            console.error('Chart element not found:', chartId);
                            return;
                        }
                        
                        var ctx = chartElement.getContext('2d');
                        
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
                    };
                    
                    // Check if Chart.js is already loaded
                    if (typeof Chart === 'undefined') {
                        // If not, load it
                        var script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        script.onload = initializeChart;
                        document.head.appendChild(script);
                    } else {
                        // If it's already loaded, just initialize
                        initializeChart();
                    }
                });
            </script>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>