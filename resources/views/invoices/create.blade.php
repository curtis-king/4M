<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Nouvelle facture</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('invoices.store') }}" x-data="invoiceForm()" class="space-y-6">
                @csrf

                <div class="bg-white shadow-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Informations générales</h3>
                        <a href="{{ route('clients.create') }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nouveau client
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2" x-data="{ search: '', open: false }" @click.away="open = false">
                            <div class="flex items-center justify-between">
                                <label for="client_id" class="block text-sm font-medium text-gray-700">
                                    Client <span x-show="!isWalkIn">*</span>
                                </label>
                                <button type="button"
                                    @click="isWalkIn = !isWalkIn; selectedClientId = ''; search = ''; agents = []; sites = []; companySiteMode = ''; companySiteOther = ''; contracts = []; selectedContractId = '';"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800">
                                    <span x-text="isWalkIn ? '← Choisir un client enregistré' : 'Client de passage (sans fiche) →'"></span>
                                </button>
                            </div>

                            <div x-show="!isWalkIn">
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        placeholder="Rechercher un client par nom, téléphone..."
                                        class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input type="hidden" name="client_id" :value="selectedClientId">
                                </div>

                                <div x-show="open && search.length > 0" x-cloak
                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                    @foreach ($clients as $client)
                                        <div class="flex items-center justify-between px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition"
                                            @click="selectedClientId = '{{ $client->id }}'; search = '{{ $client->name }}'; open = false; loadClientData();">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $client->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $client->phone }}@if($client->city) · {{ $client->city }}@endif</div>
                                            </div>
                                            @php
                                                $typeBadgeColors = [
                                                    'assureur' => 'bg-purple-100 text-purple-800',
                                                    'entreprise' => 'bg-blue-100 text-blue-800',
                                                    'particulier' => 'bg-gray-100 text-gray-600',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColors[$client->type] }}">
                                                {{ $client->display_type }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div x-show="isWalkIn" x-cloak class="mt-1">
                                <input type="text" name="walk_in_name" x-model="walkInName"
                                    placeholder="Nom du client de passage"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700">Agent / Employé</label>
                            <select name="agent_id" id="agent_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">— Aucun —</option>
                                <template x-for="agent in agents" :key="agent.id">
                                    <option :value="agent.id" x-text="agent.name + (agent.matricule ? ' (' + agent.matricule + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="recipient_type" class="block text-sm font-medium text-gray-700">Type destinataire SFEC *</label>
                            <select name="recipient_type" id="recipient_type" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="individual" {{ old('recipient_type', 'individual') === 'individual' ? 'selected' : '' }}>Particulier</option>
                                <option value="business" {{ old('recipient_type') === 'business' ? 'selected' : '' }}>Entreprise</option>
                                <option value="government" {{ old('recipient_type') === 'government' ? 'selected' : '' }}>Gouvernement</option>
                                <option value="foreign" {{ old('recipient_type') === 'foreign' ? 'selected' : '' }}>Étranger</option>
                            </select>
                        </div>
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date facture *</label>
                            <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Date échéance *</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">Devise *</label>
                            <select name="currency" id="currency" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="XAF" selected>XAF (FCFA)</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    {{-- Type de facture --}}
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Type de facture</h4>
                        <div class="flex flex-wrap gap-6">
                            <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="invoice_type" value="standard" x-model="invoiceType"
                                    class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-gray-700 font-medium">Analyses médicales</span>
                                <span class="text-xs text-gray-400">Facture ordinaire</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="invoice_type" value="controle_alimentaire" x-model="invoiceType"
                                    class="rounded-full border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-gray-700 font-medium">Contrôle Alimentaire</span>
                                <span class="text-xs text-gray-400">Analyses microbiologiques, chimiques, physiques et nutritionnelles</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex items-baseline justify-between gap-4 mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">Objet et informations de prélèvement</h4>
                            <span class="text-xs text-gray-400">Reprises dans l'en-tête de la facture</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Objet</label>
                                <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                                    placeholder="Ex. Analyse microbiologique"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label for="sample_nature" class="block text-sm font-medium text-gray-700">Nature des échantillons</label>
                                <input type="text" name="sample_nature" id="sample_nature" value="{{ old('sample_nature') }}"
                                    placeholder="Ex. Eau, aliment, surface..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label for="company_site" class="block text-sm font-medium text-gray-700">Site de l'entreprise</label>
                                <select x-model="companySiteMode" id="company_site"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="">— Aucun / sélectionner —</option>
                                    <template x-for="site in sites" :key="site.id">
                                        <option :value="site.id" x-text="site.name + (site.city ? ' · ' + site.city : '')"></option>
                                    </template>
                                    <option value="__autre__">Autre (saisie libre)…</option>
                                </select>
                                <input type="text" x-show="companySiteMode === '__autre__'" x-model="companySiteOther"
                                    placeholder="Ex. Usine Bonabéri, siège..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <input type="hidden" name="company_site" :value="companySiteValue">
                                <input type="hidden" name="site_id" :value="companySiteId">
                                <p x-show="sites.length === 0" class="mt-1 text-xs text-gray-400">Aucun site référencé pour ce client. Ajoutez-en depuis la fiche entreprise, ou utilisez « Autre ».</p>
                            </div>
                        </div>
                    </div>

                    {{-- Assurance --}}
                    <div x-show="contracts.length > 0" x-cloak class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Contrat d'assurance</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="insurance_contract_id" class="block text-sm font-medium text-gray-700">Contrat</label>
                                <select name="insurance_contract_id" id="insurance_contract_id" x-model="selectedContractId"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="">— Sans assurance —</option>
                                    <template x-for="c in contracts" :key="c.id">
                                        <option :value="c.id" x-text="c.insurer.name + ' — ' + c.coverage_rate + '% (N°' + c.contract_number + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Couverture</label>
                                <div class="mt-2 text-sm text-gray-600" x-text="selectedContract ? selectedContract.coverage_rate + '% assurance' : ''"></div>
                            </div>
                            <div>
                                <label for="pec_number" class="block text-sm font-medium text-gray-700">N° PEC</label>
                                <input type="text" name="pec_number" id="pec_number" value="{{ old('pec_number') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lignes de facture --}}
                <div class="bg-white shadow-card rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Lignes de facture</h3>
                        <button type="button" @click="addItem()" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition">
                            + Ajouter une ligne
                        </button>
                    </div>

                    <div class="space-y-2 mb-4">
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

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4 relative">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                    class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xs">✕</button>

                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Service</label>
                                        <select x-model="item.service_id" @change="onServiceChange(index)"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            <option value="">— Personnalisé —</option>
                                            <template x-for="(opts, label) in serviceGroups" :key="label">
                                                <optgroup :label="label">
                                                    <template x-for="s in opts" :key="s.id">
                                                        <option :value="s.id" x-text="(s.code ? s.code + ' — ' : '') + s.name + ' — ' + formatNumber(s.price) + ' FCFA'"></option>
                                                    </template>
                                                </optgroup>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Description *</label>
                                        <input type="text" x-model="item.description" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    </div>
                                    <div class="col-span-6 md:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                                        <select x-model="item.type" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            <option value="analyse">Analyse</option>
                                            <option value="consultation">Consultation</option>
                                            <option value="prelevement">Prélèvement</option>
                                            <option value="frais">Frais</option>
                                        </select>
                                    </div>
                                    <div class="col-span-3 md:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Qté</label>
                                        <input type="number" x-model.number="item.quantity" min="0.01" step="0.01"
                                            @input="calcLine(index)"
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm text-right">
                                    </div>
                                    <div class="col-span-3 md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Prix unit. HT</label>
                                        <input type="number" x-model.number="item.unit_price" min="0" step="100"
                                            @input="calcLine(index)"
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm text-right">
                                    </div>
                                    <div class="col-span-3 md:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Remise</label>
                                        <select x-model="item.discount_type" @change="calcLine(index)"
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            <option value="aucun">—</option>
                                            <option value="pourcentage">%</option>
                                            <option value="montant">Montant</option>
                                        </select>
                                    </div>
                                    <div class="col-span-3 md:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Val. remise</label>
                                        <input type="number" x-model.number="item.discount_value" min="0" step="100"
                                            @input="calcLine(index)"
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm text-right">
                                    </div>
                                    <div class="col-span-12 md:col-span-2 flex items-end">
                                        <div class="text-sm font-medium text-gray-900" x-text="formatNumber(item.net_amount) + ' FCFA'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="items" :value="JSON.stringify(items)">
                </div>

                {{-- Remise globale + TVA + Totaux --}}
                <div class="bg-white shadow-card rounded-2xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Remise globale</label>
                            <div class="flex gap-2">
                                <select x-model="discount_type" class="rounded-lg border-gray-300 shadow-sm text-sm">
                                    <option value="aucun">Aucune</option>
                                    <option value="pourcentage">Pourcentage (%)</option>
                                    <option value="montant">Montant (FCFA)</option>
                                </select>
                                <input type="number" x-model.number="discount_value" min="0" step="100"
                                    :disabled="discount_type === 'aucun'"
                                    class="w-32 rounded-lg border-gray-300 shadow-sm text-sm text-right">
                            </div>
                            <input type="hidden" name="discount_type" :value="discount_type">
                            <input type="hidden" name="discount_value" :value="discount_value">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Taux TVA</label>
                            <select name="tax_rate" x-model.number="tax_rate" class="rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="0">0% — Exonéré</option>
                                <option value="5">5% — Réduit</option>
                                <option value="18">18% — Normal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex justify-end space-y-2 flex-col items-end">
                            <div class="flex justify-between w-72 text-sm">
                                <span class="text-gray-500">Sous-total HT :</span>
                                <span class="font-medium" x-text="formatNumber(subtotal) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between w-72 text-sm" x-show="discountAmount > 0">
                                <span class="text-gray-500">Remise :</span>
                                <span class="font-medium text-red-600" x-text="'- ' + formatNumber(discountAmount) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between w-72 text-sm">
                                <span class="text-gray-500">TVA (<span x-text="tax_rate"></span>%) :</span>
                                <span class="font-medium" x-text="formatNumber(taxAmount) + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between w-72 text-sm border-t border-gray-300 pt-2">
                                <span class="text-gray-800 font-semibold">Total TTC :</span>
                                <span class="font-bold text-lg text-gray-900" x-text="formatNumber(total) + ' FCFA'"></span>
                            </div>
                            <div x-show="contracts.length > 0 && selectedContractId" class="flex justify-between w-72 text-sm">
                                <span class="text-blue-600">Assurance (<span x-text="selectedContract?.coverage_rate || 0"></span>%) :</span>
                                <span class="font-medium text-blue-600" x-text="formatNumber(insuranceCovered) + ' FCFA'"></span>
                            </div>
                            <div x-show="contracts.length > 0 && selectedContractId" class="flex justify-between w-72 text-sm">
                                <span class="text-orange-600 font-semibold">Ticket modérateur :</span>
                                <span class="font-bold text-orange-600" x-text="formatNumber(patientAmount) + ' FCFA'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        Créer la facture
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function invoiceForm() {
            return {
                selectedClientId: '{{ old('client_id') }}',
                isWalkIn: {{ old('walk_in_name') ? 'true' : 'false' }},
                walkInName: '{{ old('walk_in_name') }}',
                agents: [],
                contracts: [],
                selectedContractId: '{{ old('insurance_contract_id') }}',
                sites: [],
                companySiteMode: '{{ old('site_id') ? old('site_id') : (old('company_site') ? '__autre__' : '') }}',
                companySiteOther: '{{ old('site_id') ? '' : old('company_site', '') }}',
                invoiceType: '{{ old('invoice_type', 'standard') }}',
                serviceOptions: @json($serviceOptions),
                prestationSearch: '',
                catFilter: 'tous',
                @php
                    $defaultItems = old('items') ?: [
                        ['service_id' => '', 'description' => '', 'type' => 'analyse', 'quantity' => 1, 'unit_price' => 0, 'discount_type' => 'aucun', 'discount_value' => 0, 'net_amount' => 0],
                    ];
                @endphp
                items: {!! json_encode($defaultItems) !!},
                discount_type: '{{ old('discount_type', 'aucun') }}',
                discount_value: {{ old('discount_value', 0) }},
                tax_rate: {{ old('tax_rate', 18) }},

                get selectedContract() {
                    return this.contracts.find(c => c.id == this.selectedContractId) || null;
                },

                get companySiteValue() {
                    if (this.companySiteMode === '__autre__') return this.companySiteOther;
                    if (!this.companySiteMode) return '';
                    const s = this.sites.find(s => String(s.id) === String(this.companySiteMode));
                    return s ? s.name : this.companySiteOther;
                },
                get companySiteId() {
                    if (!this.companySiteMode || this.companySiteMode === '__autre__') return '';
                    return this.companySiteMode;
                },

                get filteredServices() {
                    const q = (this.prestationSearch || '').toLowerCase().trim();
                    return this.serviceOptions.filter(s => {
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
                get subtotal() {
                    return this.items.reduce((sum, i) => sum + (parseFloat(i.net_amount) || 0), 0);
                },
                get discountAmount() {
                    if (this.discount_type === 'pourcentage') return this.subtotal * (this.discount_value / 100);
                    if (this.discount_type === 'montant') return this.discount_value;
                    return 0;
                },
                get taxAmount() {
                    return (this.subtotal - this.discountAmount) * (this.tax_rate / 100);
                },
                get total() {
                    return this.subtotal - this.discountAmount + this.taxAmount;
                },
                get insuranceCovered() {
                    if (!this.selectedContract) return 0;
                    return this.total * (this.selectedContract.coverage_rate / 100);
                },
                get patientAmount() {
                    return this.total - this.insuranceCovered;
                },

                addItem() {
                    this.items.push({ service_id: '', description: '', type: 'analyse', quantity: 1, unit_price: 0, discount_type: 'aucun', discount_value: 0, net_amount: 0 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                onServiceChange(index) {
                    const item = this.items[index];
                    const s = this.serviceOptions.find(s => String(s.id) === String(item.service_id));
                    if (s) {
                        item.description = s.name;
                        item.unit_price = s.price;
                        item.type = 'analyse';
                        if (s.is_alimentaire) this.invoiceType = 'controle_alimentaire';
                    }
                    this.calcLine(index);
                },
                calcLine(index) {
                    const item = this.items[index];
                    const lineTotal = (item.quantity || 0) * (item.unit_price || 0);
                    let discount = 0;
                    if (item.discount_type === 'pourcentage') discount = lineTotal * (item.discount_value / 100);
                    if (item.discount_type === 'montant') discount = item.discount_value;
                    item.net_amount = lineTotal - discount;
                },
                loadClientData() {
                    if (!this.selectedClientId) { this.agents = []; this.sites = []; this.contracts = []; this.selectedContractId = ''; return; }
                    fetch(`/invoices/api/client/${this.selectedClientId}`)
                        .then(r => r.json())
                        .then(data => {
                            this.agents = data.client.agents || [];
                            this.sites = data.client.sites || [];
                            this.contracts = data.client.insurance_contracts || [];
                            this.selectedContractId = data.active_contract ? data.active_contract.id : '';
                            if (this.companySiteMode && this.companySiteMode !== '__autre__' && !this.sites.some(s => String(s.id) === String(this.companySiteMode))) {
                                this.companySiteMode = '__autre__';
                            }
                        });
                },
                formatNumber(n) {
                    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n || 0);
                },

                init() {
                    if (this.selectedClientId) this.loadClientData();
                }
            }
        }
    </script>
</x-app-layout>
