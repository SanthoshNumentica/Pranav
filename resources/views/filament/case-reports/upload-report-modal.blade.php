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
                <x-heroicon-o-paper-clip
                    class="w-4 h-4 text-gray-400 flex-shrink-0" />

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