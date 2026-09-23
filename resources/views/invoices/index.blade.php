<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Factures</h2>
            <div class="flex items-center gap-2">
                @can('export financial data')
                <a href="{{ route('finance.exports') }}" class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                    Export Sage
                </a>
                @endcan
                <a href="{{ route('invoices.statement.create') }}" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Facture de sommation
                </a>
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle facture
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

            @php
                $canFin = auth()->user()->can('view financial data');
                $nbCertifiees = \App\Models\Invoice::where('sfec_certified', true)->count();
                $nbSommations = \App\Models\Invoice::where('is_statement', true)->count();
                $nbControle = \App\Models\Invoice::where('invoice_type', 'controle_alimentaire')->count();
                $nbTotal = \App\Models\Invoice::count();
            @endphp

            {{-- KPI cards --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h18a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-18a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">
                        @if ($canFin)
                            {{ number_format($totalFacture, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span>
                        @else
                            {{ $nbFacturesFiltrees }}
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-gray-500">{{ $canFin ? 'Total facturé' : 'Factures (filtre)' }}</div>
                    @if ($canFin)
                        <div class="mt-1 text-xs text-gray-400">{{ $nbFacturesFiltrees }} facture(s)</div>
                    @endif
                </div>

                @if ($canFin)
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($totalEncaisse, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    <div class="mt-1 text-sm text-gray-500">Encaissé</div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($totalRestant, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    <div class="mt-1 text-sm text-gray-500">Reste à recouvrer</div>
                </div>
                @endif

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbCertifiees }}</div>
                    <div class="mt-1 text-sm text-gray-500">Certifiées SFEC</div>
                    <div class="mt-1 text-xs text-gray-400">sur {{ $nbTotal }} facture(s) au total</div>
                </div>
            </div>

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="px-6 pt-6 border-b border-gray-200 flex flex-wrap items-center gap-2">
                    <a href="{{ route('invoices.index', request()->except(['certified', 'statement', 'controle_alimentaire'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !request()->boolean('certified') && !request()->boolean('statement') && !request()->boolean('controle_alimentaire') ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Toutes <span class="ml-1 text-xs opacity-75">({{ $nbTotal }})</span>
                    </a>
                    <a href="{{ route('invoices.index', array_merge(request()->only(['search', 'status', 'date_from', 'date_to', 'client_id']), ['certified' => 1])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->boolean('certified') ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Certifiées SFEC <span class="ml-1 text-xs opacity-75">({{ $nbCertifiees }})</span>
                    </a>
                    <a href="{{ route('invoices.index', array_merge(request()->only(['search', 'status', 'date_from', 'date_to', 'client_id']), ['statement' => 1])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->boolean('statement') ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Sommations <span class="ml-1 text-xs opacity-75">({{ $nbSommations }})</span>
                    </a>
                    <a href="{{ route('invoices.index', array_merge(request()->only(['search', 'status', 'date_from', 'date_to', 'client_id']), ['controle_alimentaire' => 1])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->boolean('controle_alimentaire') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Contrôle alimentaire <span class="ml-1 text-xs opacity-75">({{ $nbControle }})</span>
                    </a>
                </div>
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex flex-wrap gap-3">
                            @if (request()->boolean('certified'))
                                <input type="hidden" name="certified" value="1">
                            @endif
                            @if (request()->boolean('statement'))
                                <input type="hidden" name="statement" value="1">
                            @endif
                            @if (request()->boolean('controle_alimentaire'))
                                <input type="hidden" name="controle_alimentaire" value="1">
                            @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° facture, bon, client..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 min-w-[200px] rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                            <option value="envoyee" {{ request('status') === 'envoyee' ? 'selected' : '' }}>Envoyée</option>
                            <option value="payee" {{ request('status') === 'payee' ? 'selected' : '' }}>Payée</option>
                            <option value="partiel" {{ request('status') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                            <option value="annulee" {{ request('status') === 'annulee' ? 'selected' : '' }}>Annulée</option>
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date début">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date fin">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                        @if (request()->hasAny(['search', 'status', 'date_from', 'date_to', 'certified', 'controle_alimentaire']))
                            @php
                                $resetParams = [];
                                if (request()->boolean('certified')) $resetParams['certified'] = 1;
                                if (request()->boolean('controle_alimentaire')) $resetParams['controle_alimentaire'] = 1;
                                if (request()->boolean('statement')) $resetParams['statement'] = 1;
                            @endphp
                            <a href="{{ route('invoices.index', $resetParams) }}"
                                class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Réinitialiser</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Facture</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                @if ($canFin)
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reste</th>
                                @endif
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                                            {{ $invoice->number }}
                                        </a>
                                        <div class="mt-0.5 flex flex-wrap gap-1">
                                            @if ($invoice->sfec_certified)
                                                <span class="inline-flex items-center gap-1 text-xs text-emerald-600" title="Certifiée SFEC">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    SFEC
                                                </span>
                                            @endif
                                            @if ($invoice->is_statement)
                                                <span class="text-xs text-purple-600">Sommation</span>
                                            @endif
                                            @if ($invoice->is_controle_alimentaire)
                                                <span class="text-xs text-emerald-600">Contrôle Alim.</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $invoice->recipient_name }}
                                        @unless ($invoice->client)
                                            <span class="text-xs text-gray-400">(passage)</span>
                                        @endunless
                                        @if ($invoice->agent)
                                            <div class="text-xs text-gray-400">{{ $invoice->agent->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">{{ $invoice->date->format('d/m/Y') }}
                                        @if ($invoice->is_statement && $invoice->statement_start_date)
                                            <div class="text-xs text-gray-400">
                                                {{ $invoice->statement_start_date->format('d/m/y') }} — {{ $invoice->statement_end_date?->format('d/m/y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_badge }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    @if ($canFin)
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                            {{ number_format($invoice->total, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-right text-emerald-600">
                                            {{ number_format($invoice->paid_amount, 0, ',', ' ') }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-right {{ $invoice->amountDue > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                            {{ number_format($invoice->amountDue, 0, ',', ' ') }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-3 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Voir">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>
                                            @if ($invoice->status !== 'annulee')
                                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Imprimer">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0M6.34 18l.229 2.523a1.125 1.125 0 001.12 1.227h8.622a1.125 1.125 0 001.12-1.227L17.66 18M6.34 18H4.75A1.75 1.75 0 013 16.25v-4.875c0-1.036.84-1.875 1.875-1.875h14.25A1.875 1.875 0 0121 11.375v4.875A1.75 1.75 0 0119.25 18H17.66M6.34 18h11.32M6.75 7.5V4.875c0-.621.504-1.125 1.125-1.125h8.25c.621 0 1.125.504 1.125 1.125V7.5" />
                                                </svg>
                                            </a>
                                            @endif
                                            @if ($invoice->status === 'brouillon')
                                                <a href="{{ route('invoices.edit', $invoice) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Modifier">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canFin ? 8 : 5 }}" class="px-6 py-16 text-center">
                                        <p class="text-sm text-gray-500">Aucune facture trouvée.</p>
                                        <a href="{{ route('invoices.create') }}" class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-800 font-medium">+ Créer une facture</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $invoices->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
