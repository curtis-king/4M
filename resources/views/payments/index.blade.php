<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-3 flex-wrap">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Paiements</h2>
            <div class="flex items-center gap-2">
                @can('export financial data')
                <a href="{{ route('finance.exports') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Export Sage
                </a>
                @endcan
                <a href="{{ route('invoices.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Factures
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $methodLabels = [
            'especes' => 'Espèces',
            'virement' => 'Virement',
            'mobile_money' => 'Mobile Money',
            'cheque' => 'Chèque',
            'carte' => 'Carte',
        ];
        $payerLabels = ['assurance' => 'Assurance', 'patient' => 'Patient'];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            {{-- KPIs --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalCollected, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    <div class="mt-1 text-sm text-gray-500">Total encaissé</div>
                    <div class="mt-1 text-xs text-gray-400">{{ $nbPayments }} paiement(s)</div>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format(($byPayer['assurance'] ?? 0), 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    <div class="mt-1 text-sm text-gray-500">Part assurance</div>
                    <div class="mt-1 text-xs text-gray-400">Mouvements de l'assurance</div>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format(($byPayer['patient'] ?? 0), 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    <div class="mt-1 text-sm text-gray-500">Part patient</div>
                    <div class="mt-1 text-xs text-gray-400">Ticket modérateur</div>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="text-2xl font-bold text-emerald-600">{{ number_format($nbPayments > 0 ? $totalCollected / $nbPayments : 0, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    <div class="mt-1 text-sm text-gray-500">Montant moyen / paiement</div>
                    <div class="mt-1 text-xs text-gray-400">Sur la période filtrée</div>
                </div>
            </div>

            {{-- Restes à payer --}}
            <div class="rounded-2xl border border-red-200 bg-red-50 p-6">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div>
                        <div class="text-sm font-semibold text-red-800">Restes à payer <span class="font-normal text-red-500">(factures non soldées)</span></div>
                        <div class="text-2xl font-bold text-red-700 mt-1">{{ number_format($totalDue, 0, ',', ' ') }} <span class="text-sm font-normal text-red-400">FCFA</span></div>
                        <div class="mt-1 text-xs text-red-600">
                            {{ $nbUnpaid }} facture(s) non soldée(s)
                            @if ($overdueTotal > 0)
                                · dont {{ number_format($overdueTotal, 0, ',', ' ') }} FCFA échus
                            @endif
                        </div>
                    </div>
                    @if ($byClient->isNotEmpty())
                        <div class="min-w-[260px] space-y-1.5">
                            @foreach ($byClient->take(5) as $row)
                                <div class="flex items-center justify-between gap-4 text-sm">
                                    <span class="text-red-700 truncate" title="{{ $row['client'] }}">{{ $row['client'] }}</span>
                                    <span class="font-semibold text-red-800 whitespace-nowrap">{{ number_format($row['due'], 0, ',', ' ') }} FCFA</span>
                                </div>
                            @endforeach
                            @if ($byClient->count() > 5)
                                <div class="text-xs text-red-400">+{{ $byClient->count() - 5 }} autre(s) client(s)</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Filtres --}}
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex flex-wrap gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° facture, bon, client..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 min-w-[200px] rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date début">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date fin">
                        <select name="method" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Tous les moyens</option>
                            @foreach ($methodLabels as $val => $label)
                                <option value="{{ $val }}" {{ request('method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="payer" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Payé par tous</option>
                            @foreach ($payerLabels as $val => $label)
                                <option value="{{ $val }}" {{ request('payer') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Tous les statuts</option>
                            @foreach (['brouillon' => 'Brouillon', 'envoyee' => 'Envoyée', 'payee' => 'Payée', 'partiel' => 'Partiel', 'annulee' => 'Annulée'] as $val => $label)
                                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="client_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Tous les clients</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                        @if (request()->hasAny(['search', 'date_from', 'date_to', 'method', 'payer', 'status', 'client_id']))
                            <a href="{{ route('payments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Réinitialiser</a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="rounded-2xl bg-white p-6 shadow-card xl:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-800">Évolution des encaissements</h3>
                    <p class="text-xs text-gray-400">Mois par mois (période filtrée)</p>
                    <div class="mt-4 h-56">
                        <canvas id="paymentsChart"></canvas>
                    </div>
                    @if ($monthlyData->isEmpty())
                        <p class="mt-2 text-xs text-gray-400">Aucune donnée pour cette période.</p>
                    @endif
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <h3 class="text-sm font-semibold text-gray-800">Répartition par moyen</h3>
                    <p class="text-xs text-gray-400">Période filtrée</p>
                    @if ($byMethod)
                    <div class="mt-4 space-y-2">
                        @foreach ($byMethod as $key => $m)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $methodLabels[$key] ?? $key }}</span>
                                <span class="font-medium text-gray-900">
                                    {{ $m['nb'] }} · {{ number_format($m['total'], 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @else
                        <p class="mt-4 text-sm text-gray-400">Aucun encaissement.</p>
                    @endif
                </div>
            </div>

            {{-- Tableau --}}
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Moyen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payé par</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reste facture</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($payments as $payment)
                                @php $invoice = $payment->invoice; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-medium text-sm">{{ $invoice->number }}</a>
                                        @if ($invoice->is_statement)
                                            <span class="ml-1 text-xs text-purple-600">Sommation</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invoice->recipient_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700">
                                            {{ $methodLabels[$payment->method] ?? $payment->method }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payerLabels[$payment->payer] ?? $payment->payer }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payment->reference ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $invoice->amountDue > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ number_format($invoice->amountDue, 0, ',', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">Aucun paiement trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4">{{ $payments->links() }}</div>
            </div>

            {{-- Factures non soldées --}}
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-gray-800">Factures non soldées (restes à payer)</h3>
                    <span class="text-xs text-gray-400">Inclut les factures sans aucun paiement enregistré</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Payé</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reste</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($unpaidInvoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-medium text-sm">{{ $invoice->number }}</a>
                                        @if ($invoice->is_statement)
                                            <span class="ml-1 text-xs text-purple-600">Sommation</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invoice->recipient_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_badge }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $invoice->due_date?->lt(now()->startOfDay()) ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                        {{ $invoice->due_date?->format('d/m/Y') }}
                                        @if ($invoice->due_date?->lt(now()->startOfDay()))
                                            <span class="ml-1 text-xs">Échue</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">{{ number_format($invoice->total, 0, ',', ' ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($invoice->paid_amount, 0, ',', ' ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">{{ number_format($invoice->amountDue, 0, ',', ' ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">Aucune facture non soldée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4">{{ $unpaidInvoices->links() }}</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mc = @json($monthlyData);
            if (Object.keys(mc).length) {
                new Chart(document.getElementById('paymentsChart'), {
                    type: 'line',
                    data: {
                        labels: Object.keys(mc),
                        datasets: [{
                            data: Object.values(mc),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#10b981',
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                            y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', callback: (v) => v.toLocaleString('fr-FR') } },
                        },
                        maintainAspectRatio: false,
                    },
                });
            }
        });
    </script>
</x-app-layout>