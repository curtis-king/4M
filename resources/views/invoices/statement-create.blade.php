<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Facture de sommation</h2>
                <p class="text-sm text-gray-500 mt-0.5">Génération de la facture mensuelle d'un assureur à partir des visites réalisées</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="statementWizard()">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif
            @if ($errors->has('discount_value'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $errors->first('discount_value') }}</div>
            @endif

            <div class="bg-white shadow-card rounded-2xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label for="insurer_id" class="block text-sm font-medium text-gray-700">Assureur *</label>
                        <select id="insurer_id" x-model="insurerId" x-on:change="companies = []; companyId = ''; agents = []; selectedIds = []; applyDefaultDiscount(); loadCompanies()"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">— Sélectionner un assureur —</option>
                            @foreach ($insurers as $insurer)
                                <option value="{{ $insurer->id }}" {{ $selectedInsurer?->id === $insurer->id ? 'selected' : '' }}>{{ $insurer->name }}</option>
                            @endforeach
                        </select>
                        @if ($insurers->isEmpty())
                            <p class="text-xs text-amber-600 mt-1">Aucun assureur. Créez d'abord des clients de type « Assureur ».</p>
                        @endif
                    </div>
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700">Mois à facturer *</label>
                        <input type="month" id="month" x-model="month"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700">Société assurée</label>
                        <select id="company_id" x-model="companyId" x-on:change="agents = []; selectedIds = []; loadTree()"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">— Choisir une société —</option>
                            <option value="all" x-show="companies.length > 1">— Toutes les sociétés —</option>
                            <template x-for="c in companies" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Zone dynamique --}}
            <template x-if="insurerId && !companies.length">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-6 text-center text-sm text-amber-700">
                    Chargement des sociétés assurées…
                </div>
            </template>

            <template x-if="insurerId && companies.length === 0">
                <div class="bg-white shadow-card rounded-2xl px-5 py-8 text-center">
                    <p class="text-sm text-gray-600 mb-1">Aucune société assurée reliée à cet assureur.</p>
                    <p class="text-xs text-gray-400">Rendez-vous sur la fiche de l'assureur pour attacher des assurés (Gérer les assurés).</p>
                </div>
            </template>

            <template x-if="companyId && agents.length">
                <form method="POST" action="{{ route('invoices.statement.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="insurer_id" :value="insurerId">
                    <input type="hidden" name="month" :value="month">

                    {{-- Visites par agent --}}
                    <div class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" x-model="agentSearch"
                                placeholder="Rechercher un employé..."
                                class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <p x-show="agentSearch && !filteredAgents.length" class="text-sm text-gray-400 text-center py-4">
                            Aucun employé ne correspond à la recherche.
                        </p>
                        <template x-for="agent in filteredAgents" :key="(agent.id ?? 'c') + agent.name">
                            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2 bg-gray-50">
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800" x-text="agent.name"></span>
                                        <span class="ml-2 text-xs text-gray-500" x-text="agent.visits.length + ' visite(s)'"></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-600">
                                        <span>Total : <strong x-text="fmt(agent.subtotal)"></strong> FCFA</span>
                                        <span class="text-blue-600">Part ass.<strong x-text="fmt(agent.insurance_total)"></strong></span>
                                        <label class="inline-flex items-center gap-1.5 ml-2">
                                            <input type="checkbox" @change="toggleAll(agent)" class="rounded border-gray-300">
                                            Tout
                                        </label>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase w-8"></th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Objet / prestation</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant complet</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Couverture</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Part assureur</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <template x-for="v in agent.visits" :key="v.id">
                                                <tr :class="isSelected(v.id) ? 'bg-blue-50' : 'hover:bg-gray-50'">
                                                    <td class="px-6 py-2">
                                                        <input type="checkbox" name="visit_ids[]" :value="v.id" :checked="isSelected(v.id)" @change="toggle(v)"
                                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-600 whitespace-nowrap" x-text="v.visit_date"></td>
                                                    <td class="px-4 py-2">
                                                        <div class="font-medium text-gray-900" x-text="v.objet"></div>
                                                        <div class="text-xs text-gray-400" x-text="v.assured_name"></div>
                                                    </td>
                                                    <td class="px-4 py-2 text-right text-gray-600" x-text="fmt(v.total)"></td>
                                                    <td class="px-4 py-2 text-right text-gray-600" x-text="v.coverage_rate + '%'"></td>
                                                    <td class="px-4 py-2 text-right font-medium text-blue-700" x-text="fmt(v.insurance_covered)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Options & totaux --}}
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="discount_value" class="block text-sm font-medium text-gray-700">
                                    Remise négociée (%)
                                    <template x-if="defaultDiscount > 0">
                                        <span class="ml-1 text-xs text-gray-400">(préconfigurée : <span x-text="defaultDiscount"></span>%)</span>
                                    </template>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="discount_value" id="discount_value"
                                    x-model.number="discountValue"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <p class="text-xs text-gray-400 mt-1">Remise minimale accordée aux assureurs : 10 %. Saisissez 0 pour aucune remise.</p>
                            </div>
                            <div class="flex justify-end items-end">
                                <div class="w-full space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Montant complet (visites retenues) :</span>
                                        <span class="font-medium" x-text="fmt(complet) + ' FCFA'"></span>
                                    </div>
                                    <div class="flex justify-between text-blue-600">
                                        <span>Part assurance (base) :</span>
                                        <span class="font-medium" x-text="fmt(base) + ' FCFA'"></span>
                                    </div>
                                    <div class="flex justify-between text-red-600" x-show="discountValue > 0">
                                        <span>Remise (<span x-text="discountValue"></span>%) :</span>
                                        <span class="font-medium" x-text="'- ' + fmt(discountAmount) + ' FCFA'"></span>
                                    </div>
                                    <div class="flex justify-between border-t border-gray-300 pt-1">
                                        <span class="font-semibold">Net à payer par l'assureur :</span>
                                        <span class="font-bold text-lg" x-text="fmt(netToPay) + ' FCFA'"></span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1" x-show="!count">Aucune visite sélectionnée.</p>
                                    <button type="submit" :disabled="!count"
                                        :class="count ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                                        class="w-full mt-2 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                                        Générer la facture (<span x-text="count"></span> visite(s))
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </template>

            <template x-if="companyId && agents.length === 0 && !loading">
                <div class="bg-white shadow-card rounded-2xl px-5 py-8 text-center">
                    <p class="text-sm text-gray-600" x-text="companyId === 'all'
                        ? 'Aucune visite réalisée et non encore facturée sur la période sélectionnée.'
                        : 'Aucune visite réalisée et non encore facturée pour cette société sur la période sélectionnée.'"></p>
                </div>
            </template>

            <template x-if="loading">
                <div class="bg-white shadow-card rounded-2xl px-5 py-8 text-center text-sm text-gray-400">Chargement…</div>
            </template>
        </div>
    </div>

    <script>
        function statementWizard() {
            return {
                insurerDiscounts: @json($insurerDiscounts),
                insurerId: '{{ $selectedInsurer?->id ?? '' }}',
                month: '{{ now()->format('Y-m') }}',
                companies: [],
                companyId: '',
                agents: [],
                agentSearch: '',
                selectedIds: [],
                discountValue: 0,
                defaultDiscount: 0,
                loading: false,

                applyDefaultDiscount() {
                    const rate = parseFloat(this.insurerDiscounts[this.insurerId] || 0);
                    this.defaultDiscount = rate;
                    this.discountValue = rate;
                },

                get complet() {
                    return this.selectedVisits().reduce((s, v) => s + (v.total || 0), 0);
                },
                get base() {
                    return this.selectedVisits().reduce((s, v) => s + (v.insurance_covered || 0), 0);
                },
                get count() {
                    return this.selectedIds.length;
                },
                get discountAmount() {
                    return this.base * ((parseFloat(this.discountValue) || 0) / 100);
                },
                get netToPay() {
                    return Math.max(0, this.base - this.discountAmount);
                },

                selectedVisits() {
                    return this.agents.flatMap(a => a.visits).filter(v => this.selectedIds.includes(v.id));
                },
                get filteredAgents() {
                    const q = (this.agentSearch || '').toLowerCase().trim();
                    if (!q) return this.agents;
                    return this.agents.filter(a => (a.name || '').toLowerCase().includes(q));
                },
                isSelected(id) {
                    return this.selectedIds.includes(id);
                },
                toggle(v) {
                    const i = this.selectedIds.indexOf(v.id);
                    if (i >= 0) this.selectedIds.splice(i, 1);
                    else this.selectedIds.push(v.id);
                },
                toggleAll(agent) {
                    const ids = agent.visits.map(v => v.id);
                    const allSelected = ids.every(id => this.selectedIds.includes(id));
                    if (allSelected) {
                        this.selectedIds = this.selectedIds.filter(id => !ids.includes(id));
                    } else {
                        this.selectedIds = [...new Set([...this.selectedIds, ...ids])];
                    }
                },

                fmt(n) {
                    return n.toLocaleString('fr-FR', { maximumFractionDigits: 0 });
                },

                loadCompanies() {
                    if (!this.insurerId) return;
                    this.loading = true;
                    fetch(`{{ route('invoices.statement.options') }}?insurer_id=${this.insurerId}&month=${this.month}`)
                        .then(r => r.json())
                        .then(d => {
                            this.companies = d.companies || [];
                            this.loading = false;
                        })
                        .catch(() => { this.loading = false; });
                },

                loadTree() {
                    if (!this.insurerId || !this.companyId) return;
                    this.loading = true;
                    this.agentSearch = '';
                    fetch(`{{ route('invoices.statement.options') }}?insurer_id=${this.insurerId}&company_id=${this.companyId}&month=${this.month}`)
                        .then(r => r.json())
                        .then(d => {
                            this.agents = d.agents || [];
                            this.loading = false;
                        })
                        .catch(() => { this.loading = false; });
                },

                init() {
                    this.applyDefaultDiscount();
                    if (this.insurerId) {
                        this.loadCompanies();
                        if (this.companyId) this.loadTree();
                    }
                    this.$watch('month', () => {
                        this.selectedIds = [];
                        if (this.companyId) this.loadTree();
                        else if (this.insurerId) this.loadCompanies();
                    });
                }
            }
        }
    </script>
</x-app-layout>