@php $canFin = auth()->user()->can('view financial data'); @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Devis</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('devis.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau devis
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- KPI cards --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h18a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-18a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">
                        @if ($canFin)
                            {{ number_format($stats['total'], 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span>
                        @else
                            {{ $statusCounts->sum() }}
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-gray-500">{{ $canFin ? 'Montant total des devis' : 'Devis au total' }}</div>
                    @if ($canFin)
                        <div class="mt-1 text-xs text-gray-400">{{ $statusCounts->sum() }} devis au total</div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">
                        @if ($canFin)
                            {{ number_format($stats['totalEnCours'], 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span>
                        @else
                            {{ $statusCounts['envoye'] + $statusCounts['brouillon'] }}
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-gray-500">Devis en attente</div>
                    <div class="mt-1 text-xs text-gray-400">{{ $statusCounts['envoye'] }} envoyé(s) · {{ $statusCounts['brouillon'] }} brouillon(s)</div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">
                        @if ($canFin)
                            {{ number_format($stats['totalAcceptes'], 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span>
                        @else
                            {{ $statusCounts['accepte'] }}
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-gray-500">Devis acceptés</div>
                    <div class="mt-1 text-xs text-gray-400">{{ $statusCounts['accepte'] }} accepté(s)</div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-5 0a3 3 0 110 6m9-3a3 3 0 11-3 3m0-3a3 3 0 013-3m-3 3a3 3 0 013 3M6 12a3 3 0 11-3 3m3-3a3 3 0 013 3" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">
                        @if ($canFin)
                            {{ number_format($stats['totalConvertis'], 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span>
                        @else
                            {{ $statusCounts['converti'] }}
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-gray-500">Convertis en facture</div>
                    <div class="mt-1 text-xs text-gray-400">{{ $statusCounts['converti'] }} converti(s) · {{ $statusCounts['refuse'] }} refusé(s)</div>
                </div>
            </div>

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="px-6 pt-6 border-b border-gray-200 flex flex-wrap items-center gap-2">
                    <a href="{{ route('devis.index', request()->except('status')) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !request('status') ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Tous <span class="ml-1 text-xs opacity-75">({{ $statusCounts->sum() }})</span>
                    </a>
                    @foreach ([
                        'brouillon' => 'Brouillon',
                        'envoye' => 'Envoyés',
                        'accepte' => 'Acceptés',
                        'refuse' => 'Refusés',
                        'converti' => 'Convertis',
                    ] as $statusKey => $statusLabel)
                        <a href="{{ route('devis.index', array_merge(request()->only(['search', 'client_id', 'date_from', 'date_to']), ['status' => $statusKey])) }}"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === $statusKey ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $statusLabel }} <span class="ml-1 text-xs opacity-75">({{ $statusCounts[$statusKey] }})</span>
                        </a>
                    @endforeach
                </div>
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex flex-wrap gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° devis, client..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 min-w-[200px] rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <select name="client_id" class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option value="">Tous les clients</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date début">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date fin">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                        @if (request()->hasAny(['search', 'status', 'client_id', 'date_from', 'date_to']))
                            <a href="{{ route('devis.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Réinitialiser</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Devis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                @if ($canFin)
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
                                @endif
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($devis as $item)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <a href="{{ route('devis.show', $item) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                                            {{ $item->number }}
                                        </a>
                                        <div class="mt-0.5 flex flex-wrap gap-1">
                                            @if ($item->invoice)
                                                <span class="inline-flex items-center gap-1 text-xs text-purple-600" title="Converti en {{ $item->invoice->number }}">
                                                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7.346 10.346a.75.75 0 00-1.212.883c.52 1.245 1.72 2.021 3.866.771l.556-.314.556.314c2.146 1.25 3.346.474 3.866-.77a.75.75 0 00-1.212-.885L14 10.5V9.75a1.5 1.5 0 011.5-1.5h.75a.75.75 0 000-1.5h-.75A3 3 0 0012 9.75v.75a.75.75 0 01-1.212.884l-.556-.314c-1.146-.67-1.773-.235-2.144.043l-.742.516z" clip-rule="evenodd"/></svg>
                                                    Facture
                                                </span>
                                            @endif
                                            @if ($item->due_date->lt(now()) && !in_array($item->status, ['converti', 'refuse']))
                                                <span class="text-xs text-red-500">Expiré</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $item->recipient_name }}
                                        @unless ($item->client)
                                            <span class="text-xs text-gray-400">(passage)</span>
                                        @endunless
                                        @if ($item->agent)
                                            <div class="text-xs text-gray-400">{{ $item->agent->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">{{ $item->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $item->due_date->format('d/m/Y') }}
                                        @if ($item->due_date->lt(now()) && !in_array($item->status, ['converti', 'refuse']))
                                            <span class="ml-1 text-xs text-red-500">(expiré)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    @if ($canFin)
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                            {{ number_format($item->total, 0, ',', ' ') }} FCFA
                                        </td>
                                    @endif
                                    <td class="px-6 py-3 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('devis.show', $item) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Voir">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>
                                            @if ($item->status === 'brouillon')
                                                <a href="{{ route('devis.edit', $item) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Modifier">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                    </svg>
                                                </a>
                                            @endif
                                            @if (in_array($item->status, ['envoye', 'accepte']) && \Illuminate\Support\Facades\Gate::allows('convert devis'))
                                                <form method="POST" action="{{ route('devis.convert', $item) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg p-1.5 text-green-600 hover:bg-green-50" title="Convertir en facture"
                                                        onclick="return confirm('Convertir ce devis en facture ?')">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-5 0a3 3 0 110 6m9-3a3 3 0 11-3 3m0-3a3 3 0 013-3m-3 3a3 3 0 013 3M6 12a3 3 0 11-3 3m3-3a3 3 0 013 3" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canFin ? 7 : 6 }}" class="px-6 py-16 text-center">
                                        <p class="text-sm text-gray-500">Aucun devis trouvé.</p>
                                        <a href="{{ route('devis.create') }}" class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-800 font-medium">+ Créer un devis</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $devis->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>