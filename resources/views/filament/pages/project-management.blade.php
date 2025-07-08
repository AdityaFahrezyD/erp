<x-filament::page>
    <style>
        body {
            background-color: #f9fafb;
        }

        .progress-bar-bg {
            background-color: #e5e7eb;
        }

        .progress-bar-fill {
            transition: width 0.3s ease-in-out;
        }

        .calendar-box {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.5rem;
            min-height: 7rem;
            overflow-y: auto;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .status-badge {
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            color: #fff;
        }

        .status-on-progress {
            background-color: #3b82f6;
        }

        .status-done {
            background-color: #10b981;
        }

        .status-default {
            background-color: #9ca3af;
        }
        .text-late{
            text-color:rgb(255, 0, 0);
        }
        .bg-late{
            text-color:rgb(158, 0, 0);
        }
    </style>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>
    @if ($overallProgress)
        <div class="mb-6">
            <div class="text-sm text-gray-600">Progress Keseluruhan Proyek</div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-purple-600 h-4 rounded-full text-white text-xs text-center" style="width: {{ $overallProgress }}%">
                    {{ $overallProgress }}%
                </div>
            </div>
        </div>
    @endif

    @if ($projectProgressStatus)
        <div class="mb-4 p-4 rounded text-white font-semibold
            @if ($projectProgressStatus === 'done') status-done
            @elseif ($projectProgressStatus === 'on progress') status-on-progress
            @else status-default @endif">
            Status Proyek: {{ ucfirst($projectProgressStatus) }}
        </div>
    @endif
    @if (!empty($progressPercentages))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-sm text-gray-600">Progress Staff</div>
                    <div class="text-xl font-bold text-blue-600">{{ $progressPercentages['staff'] }}%</div>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3 mt-1">
                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $progressPercentages['staff'] }}%"></div>
                </div>
            </div>

            <div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-sm text-gray-600">Progress Sub Modul</div>
                    <div class="text-xl font-bold text-indigo-600">{{ $progressPercentages['submodul'] }}%</div>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3 mt-1">
                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $progressPercentages['submodul'] }}%"></div>
                </div>
            </div>

            <div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-sm text-gray-600">Progress Modul</div>
                    <div class="text-xl font-bold text-green-600">{{ $progressPercentages['modul'] }}%</div>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3 mt-1">
                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $progressPercentages['modul'] }}%"></div>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($calendarData))
    @foreach ($calendarData as $month => $days)
            <h2 class="text-xl font-bold mt-6 mb-2">{{ $month }}</h2>

            <div class="grid grid-cols-7 gap-2">
                @foreach ($days as $day)
                @php
                    $endDate = \Carbon\Carbon::parse($projectEndDate);
                @endphp
                    <div class="calendar-box border p-2 rounded shadow-sm h-28 overflow-auto {{ \Carbon\Carbon::parse($day['date'])->gt($endDate) ? 'bg-late' : '' }}">
                        <div class="font-semibold text-sm">{{ $day['day'] }}</div>

                        @if (!empty($day['notes']))
                            <ul class="text-xs mt-1 list-disc list-inside text-gray-600">
                                @foreach ($day['notes'] as $modul => $submoduls)
                                    <li class="font-semibold">{{ $modul }}
                                        <ul class="list-disc list-inside ml-3">
                                            @foreach ($submoduls as $sub)
                                                <li class="{{ isset($sub['isLate']) && $sub['isLate'] ? 'text-late' : 'text-late' }} font-normal">
                                                    {{ $sub['name'] ?? $sub }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

</x-filament::page>
