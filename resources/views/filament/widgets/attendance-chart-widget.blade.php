<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-medium mb-4">Attendance Overview</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Present</th>
                        <th class="px-6 py-3">Absent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->getAttendanceData() as $row)
                        <tr class="bg-white border-b">
                            <td class="px-6 py-4">{{ $row['date'] }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                    {{ $row['present'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">
                                    {{ $row['absent'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>