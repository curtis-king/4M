<x-app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-base font-semibold text-gray-900 sm:text-lg">
                Bonjour, {{ explode(' ', Auth::user()->name)[0] }} 👋
            </h1>
            <p class="text-xs text-gray-400 sm:text-sm">
                {{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}
            </p>
        </div>
    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">

        {{-- Actions rapides --}}
        <div class="rounded-2xl bg-white shadow-card p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Actions rapides</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau client
                </a>
                <a href="{{ route('devis.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Nouveau devis
                </a>
                <a href="{{ route('visits.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Nouvelle visite
                </a>
            </div>
        </div>

        {{-- KPI compteurs (sans montants) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbClients }}</div>
                <div class="mt-1 text-sm text-gray-500">Clients</div>
                <a href="{{ route('clients.index') }}" class="mt-1 text-xs text-primary-600 hover:text-primary-700">Gérer les clients →</a>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbDevisMois }}</div>
                <div class="mt-1 text-sm text-gray-500">Devis créés ce mois</div>
                <a href="{{ route('devis.index') }}" class="mt-1 text-xs text-primary-600 hover:text-primary-700">Voir les devis →</a>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbVisitesMois }}</div>
                <div class="mt-1 text-sm text-gray-500">Visites ce mois</div>
                <a href="{{ route('visits.index') }}" class="mt-1 text-xs text-primary-600 hover:text-primary-700">Voir les visites →</a>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbVisitesJour }}</div>
                <div class="mt-1 text-sm text-gray-500">Visites aujourd'hui</div>
                <div class="mt-1 text-xs text-gray-400">{{ $nbVisitesPlanifiees }} planifiée(s) · {{ $nbVisitesRealisees }} réalisée(s)</div>
            </div>
        </div>

        {{-- Visites du jour + pipeline devis --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            @include('dashboard.partials.visites-du-jour')
            @include('dashboard.partials.devis-pipeline')
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            {{-- Clients récents --}}
            <div class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-800">Clients récents</h3>
                    <a href="{{ route('clients.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
                </div>
                @if ($clientsRecents->count())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($clientsRecents as $client)
                            <li class="flex items-center gap-3 px-6 py-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500">
                                    {{ collect(explode(' ', $client->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('clients.show', $client) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-primary-600">{{ $client->name }}</a>
                                    <span class="text-xs text-gray-400">{{ $client->phone ?? $client->display_type }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-6 py-8 text-center text-sm text-gray-400">Aucun client.</p>
                @endif
            </div>

            {{-- Factures du jour (sans montants) --}}
            <div class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-800">Factures du jour</h3>
                    <a href="{{ route('invoices.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
                </div>
                @if ($facturesDuJour->count())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($facturesDuJour as $invoice)
                            <li class="flex items-center gap-3 px-6 py-3">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-primary-600">{{ $invoice->number }}</a>
                                    <span class="text-xs text-gray-400">{{ $invoice->recipient_name }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $invoice->status_badge }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-6 py-8 text-center text-sm text-gray-400">Aucune facture aujourd'hui.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>