<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Exports comptables (Sage)</h2>
            <a href="{{ route('finance.imports') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Importer des données
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-3 rounded flex flex-wrap gap-2 items-center text-sm">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <span>
                    Exports CSV prêts pour l'assistant d'import <strong>Sage</strong> (Ciel / EBP acceptent aussi ce format).
                    Les comptes utilisés (411, 701, 44571, 571, 512…) sont paramétrables dans <a href="{{ route('settings.edit') }}" class="underline font-medium">Paramètres → Codes comptables</a>.
                </span>
            </div>

            {{-- Journal des ventes --}}
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Journal des ventes (VT)</h3>
                        <p class="text-xs text-gray-400">Une facture = écritures Débit/Crédit (411 Clients / 701 Ventes / 44571 TVA). Les factures de particuliers et de passage sont incluses.</p>
                    </div>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('finance.exports.sales') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date début</label>
                            <input type="date" name="date_from" value="{{ request('date_from', $defaultFrom) }}" class="rounded-lg border-gray-300 shadow-sm text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date fin</label>
                            <input type="date" name="date_to" value="{{ request('date_to', $defaultTo) }}" class="rounded-lg border-gray-300 shadow-sm text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                            <select name="status" class="rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="">Tous</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="envoyee">Envoyée</option>
                                <option value="payee">Payée</option>
                                <option value="partiel">Partiel</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Client</label>
                            <select name="client_id" class="rounded-lg border-gray-300 shadow-sm text-sm min-w-[180px]">
                                <option value="">Tous</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-5 py-2 rounded-lg inline-flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Télécharger le CSV
                        </button>
                    </form>
                </div>
            </div>

            {{-- Journal des encaissements --}}
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Journal des encaissements (BQ / Recettes)</h3>
                        <p class="text-xs text-gray-400">Un paiement = Débit Banque/Caisse (selon le moyen de paiement) / Crédit Clients.</p>
                    </div>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('finance.exports.receipts') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date début</label>
                            <input type="date" name="date_from" value="{{ request('date_from', $defaultFrom) }}" class="rounded-lg border-gray-300 shadow-sm text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date fin</label>
                            <input type="date" name="date_to" value="{{ request('date_to', $defaultTo) }}" class="rounded-lg border-gray-300 shadow-sm text-sm" required>
                        </div>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2 rounded-lg inline-flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Télécharger le CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>