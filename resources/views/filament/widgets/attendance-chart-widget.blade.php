<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4">
            <h2 class="text-lg font-medium mb-4">Attendance Overview</h2>
            <div class="w-full h-64">
                <canvas id="attendance-chart-{{ $this->getId() }}" class="w-full h-full"></canvas>
            </div>
        </div>
    </x-filament::section>

    @script
    <script>
        // Ensure Chart.js is loaded first
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = initChart;
            document.head.appendChild(script);
        } else {
            initChart();
        }

        function initChart() {
            // Use a unique ID to prevent conflicts
            const chartId = 'attendance-chart-{{ $this->getId() }}';
            const chartElement = document.getElementById(chartId);
            
            if (!chartElement) {
                console.error('Chart element not found:', chartId);
                return;
            }
            
            // Make sure we're dealing with a fresh canvas
            const ctx = chartElement.getContext('2d');
            const chartData = @js($this->getData());
            
            // Destroy existing chart if any
            if (window.attendanceChart) {
                window.attendanceChart.destroy();
            }
            
            // Create new chart
            window.attendanceChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
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
        }
    </script>
    @endscript
</x-filament-widgets::widget>