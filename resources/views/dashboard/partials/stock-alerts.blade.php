@if (isset($stockAlertes) && $stockAlertes->count())
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-6 shadow-soft">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-amber-800">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            Alertes stock bas
        </h3>
        <div class="flex flex-wrap gap-2">
            @foreach ($stockAlertes as $alert)
                <a href="{{ route('reagents.index') }}" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-amber-700 shadow-soft hover:bg-amber-100/60">
                    {{ $alert->name }} : {{ $alert->quantity }} {{ $alert->unit }}
                </a>
            @endforeach
        </div>
    </div>
@endif