<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('assureurs.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 leading-tight">{{ $insurer->name }}</h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs font-medium text-purple-600">Assureur</span>
                        <span class="text-gray-300">·</span>
                        <span class="text-xs text-gray-500">Assurés & taux de couverture</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.edit', $insurer) }}"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium px-3 py-2 rounded-lg transition"
                    title="Renseigner / corriger le NIU de l'assureur (requis pour la certification SFEC)">
                    Modifier la fiche
                </a>
                <a href="{{ route('invoices.statement.create', ['insurer_id' => $insurer->id]) }}"
                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Facture de sommation
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow-card rounded-2xl p-5">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Assurés</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ $contracts->count() }}</div>
                </div>
                <div class="bg-white shadow-card rounded-2xl p-5">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Contrats actifs</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $contracts->where('is_active', true)->count() }}</div>
                </div>
                <div class="bg-white shadow-card rounded-2xl p-5">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Couverture moyenne</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $contracts->count() ? round($contracts->avg('coverage_rate'), 1) : 0 }}
                        <span class="text-sm font-medium text-gray-400">%</span>
                    </div>
                </div>
            </div>

            {{-- Remise négociée --}}
            <div class="bg-white shadow-card rounded-2xl p-6 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Remise négociée</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if ($insurer->discount_rate)
                                Remise actuelle : <span class="font-semibold text-gray-900">{{ number_format((float) $insurer->discount_rate, 1, ',', ' ') }} %</span>
                                sur la part assureur, applicable dans les factures de sommation mensuelles.
                            @else
                                Aucune remise configurée. Elle sera de 0 % dans les factures de sommation.
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('assureurs.discount', $insurer) }}" class="flex items-end gap-2">
                        @csrf
                        <div>
                            <label for="discount_rate" class="block text-xs font-medium text-gray-500 mb-1">Remise (%)</label>
                            <input type="number" step="0.5" min="0" max="100" name="discount_rate" id="discount_rate"
                                value="{{ $insurer->discount_rate }}"
                                class="w-32 rounded-lg border-gray-300 shadow-sm text-sm">
                            <p class="text-xs text-gray-400 mt-1">Minimum 10 %, 0 = aucune.</p>
                        </div>
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg">Enregistrer</button>
                    </form>
                </div>
                @error('discount_rate')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-card rounded-2xl" x-data='{ q: "", filterRows() { let shown = 0; this.$el.querySelectorAll("tbody tr[data-search]").forEach(tr => { const hit = !this.q || tr.dataset.search.includes(this.q.toLowerCase()); tr.style.display = hit ? "" : "none"; if (hit) shown++; }); const no = this.$el.querySelector("#assures-no-match"); if (no) no.classList.toggle("hidden", shown > 0); } }'>
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Assurés de {{ $insurer->name }}
                        <span class="ml-1 text-xs text-gray-400">({{ $contracts->count() }})</span>
                    </h3>
                    <div class="relative flex-1 max-w-xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-model="q" @input="filterRows()" placeholder="Rechercher un assuré, n° contrat, statut..."
                            class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div x-data="{ open: false }" class="relative flex items-center gap-2">
                        <a href="{{ route('clients.create', ['type' => 'particulier']) }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium px-3 py-1.5 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Nouveau client
                        </a>
                        <button @click="open = !open"
                            class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ajouter
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                            class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-30 text-left">
                            <form method="POST" action="{{ route('assureurs.assures.store', $insurer) }}" x-data="assureForm()" class="space-y-3">
                                @csrf
                                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                    <button type="button" @click="mode = 'existing'"
                                        :class="mode === 'existing' ? 'bg-gray-800 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                        class="flex-1 py-2 text-xs font-medium transition">Assuré existant</button>
                                    <button type="button" @click="mode = 'new'"
                                        :class="mode === 'new' ? 'bg-gray-800 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                        class="flex-1 py-2 text-xs font-medium transition border-l border-gray-200">Nouvel assuré</button>
                                </div>

                                <div x-show="mode === 'existing'" x-cloak class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" x-model="candidateSearch"
                                        @focus="open = true" @input="open = true"
                                        placeholder="Rechercher un assuré..."
                                        class="block w-full rounded-lg border-gray-300 text-sm pl-10 pr-10">
                                    <input type="hidden" name="client_id" :value="candidateId">
                                    <button type="button" @click="open = !open"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <div x-show="open" x-cloak @click.outside="open = false"
                                        class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        <template x-for="c in filteredCandidates" :key="c.id">
                                            <div @click="candidateId = c.id; candidateSearch = c.name + ' (' + c.display_type + ')'; open = false"
                                                class="flex items-center justify-between px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition">
                                                <span class="text-sm font-medium text-gray-900" x-text="c.name"></span>
                                                <span class="text-xs text-gray-500" x-text="c.display_type"></span>
                                            </div>
                                        </template>
                                        <div x-show="!filteredCandidates.length" class="px-4 py-3 text-sm text-gray-400">Aucun résultat.</div>
                                    </div>
                                </div>

                                <input type="hidden" name="mode" :value="mode">

                                <div x-show="mode === 'new'" x-cloak class="space-y-2">
                                    <input type="text" name="new_name" value="{{ old('new_name') }}" placeholder="Nom complet *"
                                        class="block w-full rounded-lg border-gray-300 text-sm">
                                    <input type="text" name="new_phone" value="{{ old('new_phone') }}" placeholder="Téléphone"
                                        class="block w-full rounded-lg border-gray-300 text-sm">
                                </div>

                                <input type="text" inputmode="decimal" name="coverage_rate"
                                    value="{{ old('coverage_rate') }}" placeholder="Taux de couverture (%) *" required
                                    class="block w-full rounded-lg border-gray-300 text-sm">
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                                    Contrat actif
                                </label>
                                <div class="flex gap-2 pt-1">
                                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1.5 rounded-lg">Ajouter</button>
                                    <button type="button" @click="open = false" class="text-sm text-gray-400 hover:text-gray-600 px-3 py-1.5">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-b-2xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assuré</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Contrat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taux de couverture</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($contracts as $contract)
                                @php
                                    $badgeColors = [
                                        'entreprise' => 'text-blue-600 bg-blue-50',
                                        'particulier' => 'text-gray-500 bg-gray-50',
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50" x-data="{ editOpen: false }"
                                    data-search="{{ mb_strtolower(($contract->client?->name ?? '') . ' ' . ($contract->client?->phone ?? '') . ' ' . $contract->contract_number . ' ' . ($contract->is_active ? 'actif' : 'inactif')) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($contract->client)
                                            <a href="{{ route('clients.show', $contract->client) }}" class="text-gray-900 font-medium text-sm hover:text-blue-600">{{ $contract->client->name }}</a>
                                            <div class="mt-0.5 flex items-center gap-1">
                                                <span class="text-xs font-medium px-2 py-0.5 rounded {{ $badgeColors[$contract->client->type] ?? 'text-gray-500 bg-gray-50' }}">
                                                    {{ $contract->client->display_type }}
                                                </span>
                                                @if ($contract->client->phone)
                                                    <span class="text-xs text-gray-400">{{ $contract->client->phone }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Client supprimé</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contract->contract_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-full rounded-full {{ $contract->is_active ? 'bg-purple-500' : 'bg-gray-300' }}"
                                                    style="width: {{ min(100, (float) $contract->coverage_rate) }}%"></div>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{{ number_format((float) $contract->coverage_rate, 0, ',', ' ') }} %</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contract->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                            {{ $contract->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 relative">
                                        <button @click="editOpen = !editOpen" class="text-gray-400 hover:text-gray-700">Modifier</button>
                                        <form method="POST" action="{{ route('assureurs.assures.destroy', [$insurer, $contract]) }}" class="inline"
                                            onsubmit="return confirm('Détacher cet assuré ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500">Détacher</button>
                                        </form>

                                        <div x-show="editOpen" x-cloak @click.outside="editOpen = false"
                                            class="absolute right-0 top-8 mt-1 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-30 text-left">
                                            <form method="POST" action="{{ route('assureurs.assures.update', [$insurer, $contract]) }}" class="space-y-3">
                                                @csrf @method('PUT')
                                                <div class="text-sm font-medium text-gray-700 mb-1">
                                                    {{ $contract->client->name ?? 'Client supprimé' }} — Taux de couverture
                                                </div>
                                                <input type="text" inputmode="decimal" name="coverage_rate" value="{{ $contract->coverage_rate }}" required
                                                    class="block w-full rounded-lg border-gray-300 text-sm">
                                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                                    <input type="checkbox" name="is_active" value="1" @checked($contract->is_active) class="rounded border-gray-300">
                                                    Contrat actif
                                                </label>
                                                <div class="flex gap-2 pt-1">
                                                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1.5 rounded-lg">Enregistrer</button>
                                                    <button type="button" @click="editOpen = false" class="text-sm text-gray-400 hover:text-gray-600 px-3 py-1.5">Annuler</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="assures-empty">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <p class="text-sm text-gray-500">Aucun assuré relié à {{ $insurer->name }}.</p>
                                        <p class="text-xs text-gray-400 mt-1">Utilisez « + Ajouter » pour attacher un particulier/entreprise existant ou en créer un nouveau, et définir le taux de couverture.</p>
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="assures-no-match" class="hidden">
                                <td colspan="5" class="px-6 py-16 text-center text-sm text-gray-500">Aucun assuré ne correspond à la recherche.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Historique des sommations --}}
            <div class="bg-white shadow-card rounded-2xl mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Factures de sommation
                        <span class="ml-1 text-xs text-gray-400">({{ $statements->count() }})</span>
                    </h3>
                </div>
                @if ($statements->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net à payer</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($statements as $statement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('invoices.show', $statement) }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $statement->number }}
                                    </a>
                                    @if ($statement->sfec_certified)
                                        <span class="ml-1 text-xs text-green-600">✓</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ $statement->statement_start_date?->format('d/m/Y') }} — {{ $statement->statement_end_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statement->status_badge }}">
                                        {{ ucfirst($statement->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900">{{ number_format($statement->total, 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-green-600">{{ number_format($statement->paid_amount, 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('invoices.show', $statement) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-6 py-8 text-center text-sm text-gray-500">Aucune facture de sommation générée.</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function assureForm() {
            return {
                mode: 'existing',
                open: false,
                candidateSearch: '',
                candidateId: @json(old('client_id', '')),
                candidates: @json($candidateOptions),
                get filteredCandidates() {
                    const s = (this.candidateSearch || '').toLowerCase().trim();
                    if (!s) return this.candidates;
                    return this.candidates.filter(c =>
                        (c.name + ' ' + (c.display_type || '')).toLowerCase().includes(s)
                    );
                },
            }
        }
    </script>
</x-app-layout>