@php
    $canFin = auth()->user()->can('view financial data');
    $totalDevis = collect($devisStatusStats)->sum('count');
    $colors = [
        'brouillon' => 'bg-gray-400',
        'envoye' => 'bg-blue-500',
        'accepte' => 'bg-green-500',
        'converti' => 'bg-purple-500',
        'refuse' => 'bg-red-500',
    ];
@endphp
<div class="rounded-2xl bg-white shadow-card overflow-hidden">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-800">Pipeline des devis</h3>
        <a href="{{ route('devis.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
    </div>

    <div class="px-6 py-5 space-y-4">
        <div>
            <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>{{ $totalDevis }} devis au total</span>
                @if (! $canFin)
                    <span class="text-gray-400">Montants non visibles pour ce profil</span>
                @endif
            </div>
            <div class="flex h-2 overflow-hidden rounded-full bg-gray-100">
                @foreach (['brouillon', 'envoye', 'accepte', 'converti', 'refuse'] as $key)
                    @if (($devisStatusStats[$key]['count'] ?? 0) > 0)
                        <div class="{{ $colors[$key] }}" style="width: {{ round(($devisStatusStats[$key]['count'] / max($totalDevis, 1)) * 100) }}%"></div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="space-y-2.5">
            @forelse (['envoye', 'accepte', 'brouillon', 'converti', 'refuse'] as $key)
                @if (($devisStatusStats[$key]['count'] ?? 0) > 0)
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="h-2 w-2 rounded-full {{ $colors[$key] }}"></span>
                            {{ $devisStatusStats[$key]['label'] }}
                        </span>
                        <span class="font-medium text-gray-900">{{ $devisStatusStats[$key]['count'] }}</span>
                        <span class="w-20 text-right text-xs text-gray-400">
                            @if ($canFin)
                                {{ number_format((float) $devisStatusStats[$key]['total'], 0, ',', ' ') }} FCFA
                            @else
                                —
                            @endif
                        </span>
                    </div>
                @endif
            @empty
                <p class="text-center text-sm text-gray-400 py-4">Aucun devis.</p>
            @endforelse
        </div>
    </div>
</div>