<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Nouvelle visite</h2>
    </x-slot>

    @php
        $defaultExamRow = [[
            'service_id' => '', 'quantity' => '1', 'unit_price' => '', 'discount_type' => 'pourcentage', 'discount_value' => '0',
            'resultat_valeur' => '', 'unite_mesure' => '', 'valeur_limite' => '', 'est_conforme' => '',
        ]];
        $oldAgentIds = array_values(array_map('strval', old('agent_ids', []) ?: []));
        $selectedClient = $clients->firstWhere('id', old('client_id'));
        $clientOptions = $clients->map(fn ($c) => [
            'id' => (string) $c->id,
            'name' => $c->name,
            'type' => $c->type,
            'display_type' => $c->display_type,
        ])->all();
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <form method="POST" action="{{ route('visits.store') }}" x-data="visitForm()" class="p-6 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700">Société / Assuré *</label>
                            <div class="relative mt-1" x-data="{ open: false }" @click.away="open = false">
                                <input type="text"
                                    x-model="clientSearch"
                                    @focus="open = true"
                                    @input="open = true"
                                    placeholder="Rechercher une société / un assuré..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm pr-10">
                                <input type="hidden" name="client_id" :value="clientId">
                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </button>
                                <div x-show="open" x-cloak
                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                    <template x-for="c in filteredClients" :key="c.id">
                                        <div @click="selectClient(c)"
                                            class="flex items-center justify-between px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition">
                                            <span class="text-sm font-medium text-gray-900" x-text="c.name"></span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                :class="c.type === 'entreprise' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                                                x-text="c.display_type"></span>
                                        </div>
                                    </template>
                                    <div x-show="!filteredClients.length" class="px-4 py-3 text-sm text-gray-400">Aucun résultat.</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="contract_id" class="block text-sm font-medium text-gray-700">Contrat d'assurance</label>
                            <select name="insurance_contract_id" id="contract_id" x-model="selectedContractId"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">— Sans assurance —</option>
                                <template x-for="c in contracts" :key="c.id">
                                    <option :value="c.id" x-text="c.insurer + ' — ' + c.coverage_rate + '%'"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agents / Employés</label>
                            <div x-show="agents.length" class="mt-1 space-y-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" x-model="agentSearch"
                                        placeholder="Rechercher un employé (nom ou matricule)..."
                                        class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                                </div>
                                <div x-show="filteredAgents.length" class="flex flex-wrap gap-2">
                                    <template x-for="a in filteredAgents" :key="a.id">
                                        <label class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-50 transition"
                                            :class="selectedAgentIds.includes(String(a.id)) ? 'border-blue-300 bg-blue-50 text-blue-700' : 'text-gray-700'">
                                            <input type="checkbox" name="agent_ids[]" :value="String(a.id)" x-model="selectedAgentIds"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span x-text="a.name + (a.matricule ? ' (' + a.matricule + ')' : '')"></span>
                                        </label>
                                    </template>
                                    <span x-show="selectedAgentIds.length > 1"
                                        class="inline-flex items-center text-xs text-gray-400">
                                        <strong class="mr-1" x-text="selectedAgentIds.length"></strong> employé(s) sélectionné(s)
                                    </span>
                                </div>
                                <p x-show="!filteredAgents.length && agentSearch" class="text-xs text-gray-400">
                                    Aucun employé ne correspond à la recherche.
                                </p>
                            </div>
                            <p x-show="!agents.length" class="mt-1 text-xs text-gray-400">
                                Aucun employé pour ce client — sélectionnez une société qui en possède.
                            </p>
                        </div>
                        <div>
                            <label for="beneficiary_name" class="block text-sm font-medium text-gray-700">Employé non référentiel</label>
                            <input type="text" name="beneficiary_name" id="beneficiary_name" value="{{ old('beneficiary_name') }}"
                                placeholder="Nom si absent du référentiel..."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <p class="mt-1 text-xs text-gray-400">Facultatif — à renseigner uniquement si la personne n'existe pas dans la liste.</p>
                        </div>
                        <div>
                            <label for="objet" class="block text-sm font-medium text-gray-700">Objet de la visite</label>
                            <input type="text" name="objet" id="objet" value="{{ old('objet') }}" placeholder="Ex : Bilan annuel"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="visit_date" class="block text-sm font-medium text-gray-700">Date de visite *</label>
                            <input type="date" name="visit_date" id="visit_date" value="{{ old('visit_date', now()->format('Y-m-d')) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Statut *</label>
                            <select name="status" id="status" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="planifiee" {{ old('status', 'planifiee') === 'planifiee' ? 'selected' : '' }}>Planifiée</option>
                                <option value="realisee" {{ old('status') === 'realisee' ? 'selected' : '' }}>Réalisée</option>
                                <option value="annulee" {{ old('status') === 'annulee' ? 'selected' : '' }}>Annulée</option>
                                <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                        </div>
                    </div>

                    {{-- Lignes d'examens --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Examens / prestations</label>
                            <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                + Ajouter une ligne
                            </button>
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-600 cursor-pointer">
                                <input type="checkbox" x-model="isAlimentaire"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                Contrôle alimentaire
                            </label>
                        </div>
                        <div class="space-y-2 mb-2">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" x-model="prestationSearch"
                                    placeholder="Rechercher une prestation (nom ou code)..."
                                    class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="f in [{ key: 'tous', label: 'Toutes', cls: 'bg-blue-600 text-white' }, { key: 'medical', label: 'Analyses médicales', cls: 'bg-blue-600 text-white' }, { key: 'alimentaire', label: 'Contrôle alimentaire', cls: 'bg-emerald-600 text-white' }]" :key="f.key">
                                    <button type="button" @click="catFilter = f.key"
                                        :class="catFilter === f.key ? f.cls : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                        class="text-xs font-medium px-3 py-1.5 rounded-full transition"
                                        x-text="f.label"></button>
                                </template>
                            </div>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prestation</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">Qté</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Prix unit.</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-24">Remise %</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Net</th>
                                        <th class="px-3 py-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(row, i) in rows" :key="i">
                                        <tr>
                                            <td class="px-3 py-2">
                                                <select x-model="row.service_id" @change="row.unit_price = priceOf(row.service_id)"
                                                    :name="'exam_items[' + i + '][service_id]'"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                                    <option value="">— Prestation —</option>
                                                    <template x-for="(opts, label) in serviceGroups" :key="label">
                                                        <optgroup :label="label">
                                                            <template x-for="s in opts" :key="s.id">
                                                                <option :value="s.id" x-text="(s.code ? s.code + ' — ' : '') + s.name"></option>
                                                            </template>
                                                        </optgroup>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" step="0.01" min="0.01" x-model.number="row.quantity"
                                                    :name="'exam_items[' + i + '][quantity]'"
                                                    class="w-full text-right rounded-lg border-gray-300 shadow-sm text-sm">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" step="0.01" min="0" x-model.number="row.unit_price"
                                                    :name="'exam_items[' + i + '][unit_price]'"
                                                    class="w-full text-right rounded-lg border-gray-300 shadow-sm text-sm">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" step="0.01" min="0" max="100" x-model.number="row.discount_value"
                                                    :name="'exam_items[' + i + '][discount_value]'"
                                                    class="w-full text-right rounded-lg border-gray-300 shadow-sm text-sm">
                                                <input type="hidden" :name="'exam_items[' + i + '][discount_type]'" value="pourcentage">
                                            </td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-900" x-text="fmt(lineNet(row))"></td>
                                            <td class="px-3 py-2 text-right">
                                                <button type="button" @click="rows.splice(i, 1)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button>
                                            </td>
                                        </tr>
                                        <template x-if="isAlimentaire">
                                            <tr class="bg-emerald-50/50">
                                                <td colspan="6" class="px-3 py-2">
                                                    <div class="flex flex-wrap items-center gap-3 text-xs">
                                                        <input type="text" :name="'exam_items[' + i + '][resultat_valeur]'" x-model="row.resultat_valeur"
                                                            placeholder="Résultat" class="w-32 rounded-lg border-gray-300 shadow-sm text-sm">
                                                        <input type="text" :name="'exam_items[' + i + '][unite_mesure]'" x-model="row.unite_mesure"
                                                            placeholder="Unité" class="w-24 rounded-lg border-gray-300 shadow-sm text-sm">
                                                        <input type="text" :name="'exam_items[' + i + '][valeur_limite]'" x-model="row.valeur_limite"
                                                            placeholder="Limite réglementaire" class="w-36 rounded-lg border-gray-300 shadow-sm text-sm">
                                                        <select :name="'exam_items[' + i + '][est_conforme]'" x-model="row.est_conforme"
                                                            class="rounded-lg border-gray-300 shadow-sm text-sm">
                                                            <option value="">Conformité —</option>
                                                            <option value="1">Conforme</option>
                                                            <option value="0">Non conforme</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                    <tr x-show="!rows.length">
                                        <td colspan="6" class="px-3 py-6 text-center text-gray-400 text-sm">Aucune prestation ajoutée.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Contrôle alimentaire --}}
                    <div x-show="isAlimentaire" x-transition class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-emerald-800">Informations produits alimentaires</h3>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium px-2 py-0.5">Contrôle alimentaire</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="produit_alimentaire" class="block text-sm font-medium text-gray-700">Produit alimentaire *</label>
                                <input type="text" name="produit_alimentaire" id="produit_alimentaire" value="{{ old('produit_alimentaire') }}"
                                    placeholder="Ex : Eau minérale, lait, viande..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label for="numero_lot" class="block text-sm font-medium text-gray-700">Numéro de lot</label>
                                <input type="text" name="numero_lot" id="numero_lot" value="{{ old('numero_lot') }}" placeholder="Ex : LOT-2026-045"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label for="origine_produit" class="block text-sm font-medium text-gray-700">Origine du produit</label>
                                <input type="text" name="origine_produit" id="origine_produit" value="{{ old('origine_produit') }}" placeholder="Ex : Brasseur de Brazzaville"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label for="date_prelevement" class="block text-sm font-medium text-gray-700">Date de prélèvement</label>
                                <input type="date" name="date_prelevement" id="date_prelevement" value="{{ old('date_prelevement') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label for="type_controle" class="block text-sm font-medium text-gray-700">Type de contrôle</label>
                                <select name="type_controle" id="type_controle"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="">— Sélectionner —</option>
                                    <option value="routine" {{ old('type_controle') === 'routine' ? 'selected' : '' }}>Routine</option>
                                    <option value="surveillance" {{ old('type_controle') === 'surveillance' ? 'selected' : '' }}>Surveillance</option>
                                    <option value="plainte" {{ old('type_controle') === 'plainte' ? 'selected' : '' }}>Plainte</option>
                                    <option value="certification" {{ old('type_controle') === 'certification' ? 'selected' : '' }}>Certification</option>
                                </select>
                            </div>
                            <div>
                                <label for="statut_resultat" class="block text-sm font-medium text-gray-700">Résultat global</label>
                                <select name="statut_resultat" id="statut_resultat"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="">— Sélectionner —</option>
                                    <option value="en_attente" {{ old('statut_resultat') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                    <option value="conforme" {{ old('statut_resultat') === 'conforme' ? 'selected' : '' }}>Conforme</option>
                                    <option value="non_conforme" {{ old('statut_resultat') === 'non_conforme' ? 'selected' : '' }}>Non conforme</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="discount_value" class="block text-sm font-medium text-gray-700">Remise globale (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="discount_value" id="discount_value"
                                x-model.number="globalDiscount"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="patient_paid" class="block text-sm font-medium text-gray-700">Montant reçu du patient (FCFA)</label>
                            <input type="number" step="0.01" min="0" name="patient_paid" id="patient_paid" value="{{ old('patient_paid') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <p class="text-xs text-gray-400 mt-1" x-show="selectedContractId">Part patient attendue : <span x-text="fmt(patientPart)"></span> FCFA</p>
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Totaux prévisuels --}}
                    <div class="flex justify-end">
                        <div class="w-72 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Sous-total :</span>
                                <span class="font-medium" x-text="fmt(subtotal) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between" x-show="globalDiscount > 0">
                                <span class="text-red-600">Remise :</span>
                                <span class="text-red-600 font-medium" x-text="'- ' + fmt(globalDiscountAmount) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-300 pt-1">
                                <span class="font-semibold">Total :</span>
                                <span class="font-bold text-lg" x-text="fmt(netTotal) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between text-blue-600" x-show="selectedContractId">
                                <span>Part assurance (<span x-text="coverageRate"></span>%) :</span>
                                <span class="font-medium" x-text="fmt(insurancePart) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between text-orange-600" x-show="selectedContractId">
                                <span>Ticket modérateur :</span>
                                <span class="font-bold" x-text="fmt(patientPart) + ' FCFA'"></span>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('visits.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Créer la visite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function visitForm() {
            return {
                clientId: '{{ old('client_id') }}',
                clientSearch: @json($selectedClient?->name ?? ''),
                selectedContractId: '{{ old('insurance_contract_id') }}',
                selectedAgentIds: @json($oldAgentIds),
                clients: @json($clientOptions),
                contracts: [],
                agents: [],
                agentSearch: '',
                services: @json($serviceOptions),
                prestationSearch: '',
                catFilter: 'tous',
                rows: @json(old('exam_items', $defaultExamRow)),
                globalDiscount: @json((float)(old('discount_value', 0))),
                isAlimentaire: @json((bool) (old('produit_alimentaire') || old('numero_lot') || old('statut_resultat'))),
                coverageRate: 0,

                get coverage() {
                    return this.contracts.find(c => String(c.id) === String(this.selectedContractId)) || null;
                },

                get filteredClients() {
                    const q = (this.clientSearch || '').toLowerCase().trim();
                    if (!q) return this.clients;
                    return this.clients.filter(c =>
                        c.name.toLowerCase().includes(q) || (c.display_type || '').toLowerCase().includes(q)
                    );
                },

                get filteredAgents() {
                    const q = (this.agentSearch || '').toLowerCase().trim();
                    if (!q) return this.agents;
                    return this.agents.filter(a =>
                        (a.name || '').toLowerCase().includes(q) || (a.matricule || '').toLowerCase().includes(q)
                    );
                },

                get filteredServices() {
                    const q = (this.prestationSearch || '').toLowerCase().trim();
                    return this.services.filter(s => {
                        if (this.catFilter === 'medical' && s.is_alimentaire) return false;
                        if (this.catFilter === 'alimentaire' && !s.is_alimentaire) return false;
                        if (q && !(s.name.toLowerCase().includes(q) || (s.code || '').toLowerCase().includes(q))) return false;
                        return true;
                    });
                },

                get serviceGroups() {
                    return this.filteredServices.reduce((acc, s) => {
                        (acc[s.category_name] = acc[s.category_name] || []).push(s);
                        return acc;
                    }, {});
                },

                selectClient(c) {
                    this.clientId = c.id;
                    this.clientSearch = c.name;
                    this.loadClientData();
                },

                priceOf(id) {
                    const s = this.services.find(s => String(s.id) === String(id));
                    return s ? s.price : 0;
                },

                lineTotal(row) {
                    return (parseFloat(row.quantity) || 0) * (parseFloat(row.unit_price) || 0);
                },
                lineDiscount(row) {
                    const pct = parseFloat(row.discount_value) || 0;
                    return this.lineTotal(row) * pct / 100;
                },
                lineNet(row) {
                    return Math.max(0, this.lineTotal(row) - this.lineDiscount(row));
                },

                get subtotal() {
                    return this.rows.reduce((sum, row) => sum + this.lineNet(row), 0);
                },
                get globalDiscountAmount() {
                    return this.subtotal * (parseFloat(this.globalDiscount) || 0) / 100;
                },
                get netTotal() {
                    return Math.max(0, this.subtotal - this.globalDiscountAmount);
                },
                get insurancePart() {
                    return this.netTotal * (this.coverage ? parseFloat(this.coverage.coverage_rate) : 0) / 100;
                },
                get patientPart() {
                    return this.netTotal - this.insurancePart;
                },

                fmt(n) {
                    return n.toLocaleString('fr-FR', { maximumFractionDigits: 2 });
                },

                addRow() {
                    this.rows.push({ service_id: '', quantity: '1', unit_price: '', discount_type: 'pourcentage', discount_value: '0',
                        resultat_valeur: '', unite_mesure: '', valeur_limite: '', est_conforme: '' });
                },

                loadClientData() {
                    this.contracts = [];
                    this.agents = [];
                    this.agentSearch = '';
                    this.selectedAgentIds = [];
                    this.selectedContractId = '';
                    if (!this.clientId) {
                        this.coverage = null;
                        return;
                    }
                    fetch(`/invoices/api/client/${this.clientId}`).then(r => r.json()).then(d => {
                        this.agents = d.client.agents || [];
                        this.contracts = (d.client.insurance_contracts || [])
                            .filter(c => c.is_active)
                            .map(c => ({
                                id: c.id,
                                insurer: c.insurer ? c.insurer.name : 'Assureur',
                                coverage_rate: c.coverage_rate,
                                is_active: c.is_active,
                            }));
                        if (this.contracts.length) {
                            const active = d.active_contract;
                            this.selectedContractId = active ? String(active.id) : '';
                        }
                    });
                },

                init() {
                    if (this.clientId) this.loadClientData();
                    this.$watch('selectedContractId', () => {
                        this.coverageRate = this.coverage ? parseFloat(this.coverage.coverage_rate) : 0;
                    });
                    this.$watch('isAlimentaire', (v) => {
                        if (v) this.catFilter = 'alimentaire';
                        else if (this.catFilter === 'alimentaire') this.catFilter = 'tous';
                    });
                    this.$watch('clientSearch', (v) => {
                        if (v === '') {
                            this.clientId = '';
                            this.loadClientData();
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>