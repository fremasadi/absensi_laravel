<x-filament-widgets::widget>
    <x-filament::section>
        <div>
            <h2 class="text-lg font-medium">Attendance Overview</h2>
            <div>
                <canvas id="attendanceChart" style="width: 100%; height: 300px;"></canvas>
            </div>
        </div>
    </x-filament::section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const data = @json($this->getData());
            
            new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</x-filament-widgets::widget>