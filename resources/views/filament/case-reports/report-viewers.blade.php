<div class="space-y-6">

    {{-- Viewer Buttons --}}
    @if(!empty($record->study_instance_uid))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

        {{-- MPR & 3D Viewer --}}
        <x-filament::button
            color="primary"
            class="w-full"
            tag="a"
            href="{{ $this->orthancUrl }}/ohif/viewer?hangingprotocolId=mprAnd3DVolumeViewport&StudyInstanceUIDs={{ $record->study_instance_uid }}"
            target="_blank">
            MPR & 3D Viewer
        </x-filament::button>

        {{-- Stone Viewer --}}
        <x-filament::button
            color="success"
            class="w-full"
            tag="a"
            href="{{ $this->orthancUrl }}/stone-webviewer/index.html?study={{ $record->study_instance_uid }}"
            target="_blank">
            Stone Viewer
        </x-filament::button>

        {{-- Standard OHIF Viewer --}}
        <x-filament::button
            color="warning"
            class="w-full"
            tag="a"
            href="{{ $this->orthancUrl }}/ohif/viewer?StudyInstanceUIDs={{ $record->study_instance_uid }}"
            target="_blank">
            Standard Viewer
        </x-filament::button>

        {{-- Volume Viewer --}}
        <x-filament::button
            color="danger"
            class="w-full"
            tag="a"
            href="{{ $this->orthancUrl }}/volview/index.html?names=%5Barchive.zip%5D&urls=%5B../studies/{{ $record->study_instance_uid }}/archive%5D"
            target="_blank">
            Volume Viewer
        </x-filament::button>

        {{-- Segmentation Viewer --}}
        <x-filament::button
            color="info"
            class="w-full"
            tag="a"
            href="{{ $this->orthancUrl }}/ohif/segmentation?StudyInstanceUIDs={{ $record->study_instance_uid }}"
            target="_blank">
            Segmentation
        </x-filament::button>

    </div>
    @else
    <p class="text-sm text-gray-500">No study available to view.</p>
    @endif

    <hr class="border-gray-200">

    {{-- Documents Section --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold">
            Scan Report Documents
        </h3>

        @forelse ($record->items as $item)
        <div class="rounded-lg border p-4 space-y-2">
            <div class="text-sm font-medium text-gray-700">
                Scan Type: {{ optional($item->scanType)->name ?? '-' }}
                |
                Scan: {{ optional($item->scan)->name ?? '-' }}
            </div>

            {{-- Documents --}}
            @if (is_array($item->documents) && count($item->documents))
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($item->documents as $uuid => $path)
                <li class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-400 flex-shrink-0" />
                        <span
                            class="truncate max-w-[260px] text-gray-700"
                            title="{{ basename($path) }}">
                            {{ basename($path) }}
                        </span>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-xs text-gray-400">
                No documents uploaded
            </p>
            @endif
        </div>
        @empty
        <p class="text-sm text-gray-500">
            No scan items available.
        </p>
        @endforelse
    </div>
</div>