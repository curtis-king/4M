@php
    $statusStyles = [
        'planifiee' => ['label' => 'Planifiée', 'badge' => 'bg-blue-100 text-blue-700', 'dot' => 'bg-blue-500'],
        'realisee' => ['label' => 'Réalisée', 'badge' => 'bg-green-100 text-green-700', 'dot' => 'bg-green-500'],
        'annulee' => ['label' => 'Annulée', 'badge' => 'bg-red-100 text-red-700', 'dot' => 'bg-red-500'],
        'absent' => ['label' => 'Absent', 'badge' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500'],
    ];
@endphp
<div class="rounded-2xl bg-white shadow-card overflow-hidden">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-800">Visites du jour</h3>
        <a href="{{ route('visits.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
    </div>

    @if ($visitesAujourdhui->count())
        <ul class="divide-y divide-gray-100">
            @foreach ($visitesAujourdhui as $visit)
                @php $style = $statusStyles[$visit->status] ?? ['label' => ucfirst($visit->status), 'badge' => 'bg-gray-100 text-gray-600', 'dot' => 'bg-gray-400']; @endphp
                <li class="flex items-center gap-3 px-6 py-3">
                    <span class="h-2 w-2 shrink-0 rounded-full {{ $style['dot'] }}"></span>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('visits.show', $visit) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-primary-600">
                            {{ $visit->assured_name }}
                        </a>
                        <span class="text-xs text-gray-400">{{ $visit->company_name }}</span>
                    </div>
                    <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded {{ $style['badge'] }}">
                        {{ $style['label'] }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="px-6 py-8 text-center text-sm text-gray-400">Aucune visite aujourd'hui.</p>
    @endif
</div>