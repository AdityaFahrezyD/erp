<x-filament::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @if (!empty($calendarData))
        @foreach ($calendarData as $month => $days)
            <h2 class="text-xl font-bold mt-6 mb-2">{{ $month }}</h2>

            <div class="grid grid-cols-7 gap-2">
                @foreach ($days as $day)
                    <div class="border p-2 rounded shadow-sm h-28 overflow-auto">
                        <div class="font-semibold text-sm">{{ $day['day'] }}</div>
                            @if (!empty($day['notes']))
                                <ul class="text-xs mt-1 list-disc list-inside text-gray-600">
                                    @foreach ($day['notes'] as $modul => $submoduls)
                                        <li class="font-semibold">{{ $modul }}
                                            <ul class="list-disc list-inside ml-3">
                                                @foreach ($submoduls as $sub)
                                                    <li class="text-gray-500 font-normal">{{ $sub }}</li>
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
