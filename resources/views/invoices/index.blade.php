<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Factures</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('invoices.statement.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    + Facture de sommation
                </a>
                <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    + Nouvelle facture
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="px-6 pt-6 border-b border-gray-200 flex items-center gap-2">
                    <a href="{{ route('invoices.index', request()->except(['certified', 'statement', 'controle_alimentaire'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !request()->boolean('certified') && !request()->boolean('statement') && !request()->boolean('controle_alimentaire') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Toutes
                    </a>
                    <a href="{{ route('invoices.index', array_merge(request()->only(['search', 'status', 'date_from', 'date_to', 'client_id']), ['certified' => 1])) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->boolean('certified') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Certifiées SFEC
                        <span class="text-xs {{ request()->boolean('certified') ? 'text-blue-100' : 'text-green-600' }}">✓</span>
                    </a>
                    <a href="{{ route('invoices.index', array_merge(request()->only(['search', 'status', 'date_from', 'date_to', 'client_id']), ['statement' => 1])) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->boolean('statement') ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Sommations
                    </a>
                    <a href="{{ route('invoices.index', array_merge(request()->only(['search', 'status', 'date_from', 'date_to', 'client_id']), ['controle_alimentaire' => 1])) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->boolean('controle_alimentaire') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Contrôle alimentaire
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
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reste</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-medium text-sm">
                                            {{ $invoice->number }}
                                        </a>
                                        @if ($invoice->sfec_certified)
                                            <span class="ml-1 text-xs text-green-600" title="Certifiée SFEC">✓ SFEC</span>
                                        @endif
                                        @if ($invoice->is_statement)
                                            <span class="ml-1 text-xs text-purple-600" title="Facture de sommation">Sommation</span>
                                        @endif
                                        @if ($invoice->is_controle_alimentaire)
                                            <span class="ml-1 text-xs text-emerald-600" title="Contrôle Alimentaire">Contrôle Alim.</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $invoice->recipient_name }}
                                        @unless ($invoice->client)
                                            <span class="text-xs text-gray-400">(passage)</span>
                                        @endunless
                                        @if ($invoice->agent)
                                            <div class="text-xs text-gray-400">{{ $invoice->agent->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invoice->date->format('d/m/Y') }}
                                        @if ($invoice->is_statement && $invoice->statement_start_date)
                                            <div class="text-xs text-gray-400">
                                                {{ $invoice->statement_start_date->format('d/m/y') }} — {{ $invoice->statement_end_date?->format('d/m/y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_badge }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        {{ number_format($invoice->total, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                        {{ number_format($invoice->paid_amount, 0, ',', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $invoice->amountDue > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ number_format($invoice->amountDue, 0, ',', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                        @if ($invoice->status === 'brouillon')
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">Aucune facture trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">
                    {{ $invoices->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
