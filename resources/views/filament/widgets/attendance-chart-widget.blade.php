<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-medium mb-4">Attendance Overview</h2>
        
        <div class="overflow-x-auto">
            <div class="grid grid-cols-7 gap-2">
                @foreach($this->getAttendanceData() as $row)
                    <div class="bg-white rounded p-3 border text-center">
                        <div class="font-medium text-gray-900">{{ $row['date'] }}</div>
                        <div class="flex justify-center space-x-2 mt-2">
                            <div class="bg-green-100 text-green-800 rounded-full px-3 py-1 text-xs">
                                Present: {{ $row['present'] }}
                            </div>
                            <div class="bg-red-100 text-red-800 rounded-full px-3 py-1 text-xs">
                                Absent: {{ $row['absent'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>