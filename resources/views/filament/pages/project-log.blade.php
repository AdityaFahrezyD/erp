<x-filament::page>
    <style>
        .drop-zone {
            transition: background-color 0.2s, border-color 0.2s;
        }

        .drop-zone.drag-hover {
            background-color: #e0f2fe; /* Tailwind blue-100 */
            border: 2px dashed #3b82f6; /* Tailwind blue-500 */
        }

        .kanban-card {
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .kanban-card.dragging {
            opacity: 0.5;
            transform: scale(1.03);
        }
    </style>

    <div class="mb-4">
        {{ $this->form }}
    </div>

    @if (!empty($cards))
        <div class="flex gap-4 overflow-auto">
            @foreach ($this->getStatuses() as $statusKey => $statusLabel)
                <div class="min-w-[300px] flex flex-col bg-gray-100 rounded shadow p-2 flex-1">
                    <h3 class="font-bold text-center text-sm mb-2">{{ $statusLabel }}</h3>

                    <div
                        class="drop-zone flex-1 flex flex-col gap-2"
                        ondrop="handleDrop(event, '{{ $statusKey }}')"
                        ondragover="allowDrop(event)"
                        ondragleave="handleDragLeave(event)"
                    >
                        @forelse ($cards[$statusKey] ?? [] as $item)
                            <div
                                class="kanban-card bg-white p-2 rounded shadow text-sm cursor-move"
                                draggable="true"
                                ondragstart="handleDragStart(event, '{{ $item['id'] }}')"
                                ondragend="handleDragEnd(event)"
                            >
                                <strong>{{ $item['modul'] }}</strong><br>
                                <span class="text-gray-600">{{ $item['sub_modul'] }}</span><br>
                                <span class="text-xs text-gray-400">Staff: {{ $item['staff'] }}</span>
                            </div>
                        @empty
                            <div class="h-full rounded border-2 border-dashed border-gray-300"></div>
                        @endforelse
                    </div>
                </div>


            @endforeach
        </div>
    @else
        <div class="text-center text-gray-500">Silakan pilih project dan modul terlebih dahulu.</div>
    @endif

    <script>
        let draggedId = null;

        function allowDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.add('drag-hover');
        }

        function handleDragLeave(event) {
            event.currentTarget.classList.remove('drag-hover');
        }

        function handleDragStart(event, id) {
            draggedId = id;
            event.dataTransfer.effectAllowed = 'move';

            // Tambahkan class animasi pada elemen yang sedang di-drag
            event.target.classList.add('dragging');
        }

        function handleDragEnd(event) {
            event.target.classList.remove('dragging');
        }

        function handleDrop(event, newStatus) {
            event.preventDefault();
            event.currentTarget.classList.remove('drag-hover');

            if (!draggedId) return;

            @this.call('updateCardStatus', draggedId, newStatus);
            draggedId = null;
        }
    </script>


</x-filament::page>
