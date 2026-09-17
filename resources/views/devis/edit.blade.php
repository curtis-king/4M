<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Modifier le devis {{ $devis->number }}</h2>
    </x-slot>

    @php
        $clientOptions = $clients->map(fn ($c) => [
            'id' => (string) $c->id,
            'name' => $c->name,
            'type' => $c->type,
            'display_type' => $c->display_type,
        ])->all();
    @endphp

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

            <form method="POST" action="{{ route('devis.update', $devis) }}" x-data="devisForm()" class="space-y-6">
                @csrf @method('PUT')

                <div class="bg-white shadow-card rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="client_id" class="block text-sm font-medium text-gray-700">
                                    Client <span x-show="!isWalkIn">*</span>
                                </label>
                                <button type="button"
                                    @click="isWalkIn = !isWalkIn; selectedClientId = ''; clientSearch = ''; agents = [];"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800">
                                    <span x-text="isWalkIn ? '← Client enregistré' : 'Client de passage →'"></span>
                                </button>
                            </div>
                            <div x-show="!isWalkIn" class="relative mt-1" x-data="{ open: false }" @click.away="open = false">
                                <input type="text"
                                    x-model="clientSearch"
                                    @focus="open = true"
                                    @input="open = true"
                                    placeholder="Rechercher une société / un assuré..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm pr-10">
                                <input type="hidden" name="client_id" :value="selectedClientId">
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
                            <input type="text" x-show="isWalkIn" x-cloak name="walk_in_name" x-model="walkInName"
                                placeholder="Nom du client de passage"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
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
                            <label for="date" class="block text-sm font-medium text-gray-700">Date devis *</label>
                            <input type="date" name="date" id="date" value="{{ $devis->date->format('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Date de validité *</label>
                            <input type="date" name="due_date" id="due_date" value="{{ $devis->due_date->format('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">Devise *</label>
                            <select name="currency" id="currency" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="XAF" {{ $devis->currency === 'XAF' ? 'selected' : '' }}>XAF (FCFA)</option>
                                <option value="USD" {{ $devis->currency === 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Lignes de devis --}}
                <div class="bg-white shadow-card rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Lignes de devis</h3>
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
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ $devis->notes }}</textarea>
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
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('devis.show', $devis) }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function devisForm() {
            return {
                selectedClientId: '{{ $devis->client_id }}',
                clientSearch: @json($selectedClient?->name ?? ''),
                clients: @json($clientOptions),
                isWalkIn: {{ $devis->client_id ? 'false' : 'true' }},
                walkInName: '{{ $devis->walk_in_name }}',
                agents: @json($selectedClient->agents ?? []),
                serviceOptions: @json($serviceOptions),
                prestationSearch: '',
                catFilter: 'tous',
                items: @json($devis->items),
                discount_type: '{{ $devis->discount_type }}',
                discount_value: {{ $devis->discount_value }},
                tax_rate: {{ $devis->tax_rate }},

                get filteredClients() {
                    const q = (this.clientSearch || '').toLowerCase().trim();
                    if (!q) return this.clients;
                    return this.clients.filter(c =>
                        c.name.toLowerCase().includes(q) || (c.display_type || '').toLowerCase().includes(q)
                    );
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
                selectClient(c) {
                    this.selectedClientId = c.id;
                    this.clientSearch = c.name;
                    this.loadClientData();
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
                    if (!this.selectedClientId) { this.agents = []; return; }
                    fetch(`/devis/api/client/${this.selectedClientId}`)
                        .then(r => r.json())
                        .then(data => {
                            this.agents = data.client.agents || [];
                        });
                },
                formatNumber(n) {
                    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n || 0);
                },
            }
        }
    </script>
</x-app-layout>