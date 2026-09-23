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

        {{-- KPI cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h18a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-18a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($ca, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                <div class="mt-1 text-sm text-gray-500">Chiffre d'affaires total</div>
                <div class="mt-1 text-xs text-emerald-600">Payé : {{ number_format($caPaye, 0, ',', ' ') }} FCFA</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6l9.75 6.75L21.75 6M2.25 6v12a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V6m-19.5 0a1.5 1.5 0 011.5-1.5h16.5a1.5 1.5 0 011.5 1.5" />
                        </svg>
                    </div>
                    @if (!is_null($caEvolutionPct))
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $caEvolutionPct >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                @if ($caEvolutionPct >= 0)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.306a11.95 11.95 0 015.814 5.518l2.74 1.22m0 0l-5.94 2.281m5.94-2.28l-2.28-5.941" />
                                @endif
                            </svg>
                            {{ abs($caEvolutionPct) }}%
                        </span>
                    @endif
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($caMois, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                <div class="mt-1 text-sm text-gray-500">Chiffre d'affaires du mois</div>
                <div class="mt-1 text-xs text-gray-400">{{ $nbFacturesMois }} facture(s) ce mois</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $facturesEnAttente }}</div>
                <div class="mt-1 text-sm text-gray-500">Factures en attente</div>
                <div class="mt-1 text-xs text-gray-400">+ {{ $facturesBrouillon }} brouillon(s)</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbClients }}</div>
                <div class="mt-1 text-sm text-gray-500">Clients</div>
                <div class="mt-1 text-xs text-gray-400">+ {{ $nouveauxClientsMois }} ce mois</div>
            </div>
        </div>

        {{-- Analytics --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow-card xl:col-span-2">
                <h3 class="text-sm font-semibold text-gray-800">Évolution du chiffre d'affaires</h3>
                <p class="text-xs text-gray-400">6 derniers mois</p>
                <div class="mt-4 h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <h3 class="text-sm font-semibold text-gray-800">Répartition des factures</h3>
                <p class="text-xs text-gray-400">Par statut de paiement</p>
                <div class="mt-4 h-40">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach ($invoiceStatusStats as $key => $stat)
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2 w-2 rounded-full {{ $key === 'payee' ? 'bg-emerald-500' : ($key === 'partiel' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ $stat['label'] }}
                            </span>
                            <span class="font-medium text-gray-900">{{ $stat['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card xl:col-span-3">
                <h3 class="text-sm font-semibold text-gray-800">Évolution du nombre d'examens</h3>
                <p class="text-xs text-gray-400">6 derniers mois</p>
                <div class="mt-4 h-56">
                    <canvas id="examsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Pipeline devis + alertes stock --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('dashboard.partials.devis-pipeline')
            </div>
            <div class="space-y-5">
                @include('dashboard.partials.stock-alerts')
            </div>
        </div>

        {{-- Raccourcis administration --}}
        @if (Auth::user()->can('manage users') || Auth::user()->can('manage roles'))
            <div class="rounded-2xl bg-white shadow-card p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Administration</h3>
                <div class="flex flex-wrap gap-3">
                    @if (Auth::user()->can('manage users'))
                        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                            Gérer les utilisateurs
                        </a>
                    @endif
                    @if (Auth::user()->can('manage roles'))
                        <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                            Rôles & permissions
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Dernières factures --}}
        <div class="rounded-2xl bg-white shadow-card">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-800">Dernières factures</h3>
                <a href="{{ route('invoices.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
            </div>
            @if ($facturesRecents->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">N° Facture</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Examens</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Paiement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $methodLabels = [
                                'especes' => 'Espèces',
                                'virement' => 'Virement',
                                'mobile_money' => 'Mobile Money',
                                'cheque' => 'Chèque',
                                'carte' => 'Carte',
                            ];
                        @endphp
                        @foreach ($facturesRecents as $f)
                        @php
                            $examCount = $f->items->where('type', 'analyse')->count();
                            $lastPayment = $f->payments->first();
                        @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-6 py-3 font-medium">
                                <a href="{{ route('invoices.show', $f) }}" class="text-primary-600 hover:text-primary-700">{{ $f->number }}</a>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $f->recipient_name }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $f->date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-gray-500" title="{{ $f->items->pluck('description')->implode(', ') }}">
                                {{ $examCount }} examen{{ $examCount > 1 ? 's' : '' }}
                            </td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900">{{ number_format($f->total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-3 text-gray-500">{{ $lastPayment ? ($methodLabels[$lastPayment->method] ?? $lastPayment->method) : '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $f->status_badge }}">
                                    {{ ucfirst($f->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('invoices.show', $f) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Voir">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('invoices.print', $f) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Imprimer">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0M6.34 18l.229 2.523a1.125 1.125 0 001.12 1.227h8.622a1.125 1.125 0 001.12-1.227L17.66 18M6.34 18H4.75A1.75 1.75 0 013 16.25v-4.875c0-1.036.84-1.875 1.875-1.875h14.25A1.875 1.875 0 0121 11.375v4.875A1.75 1.75 0 0119.25 18H17.66M6.34 18h11.32M6.75 7.5V4.875c0-.621.504-1.125 1.125-1.125h8.25c.621 0 1.125.504 1.125 1.125V7.5" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="px-6 py-8 text-center text-sm text-gray-400">Aucune facture.</p>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const revenueData = @json($revenueByMonth);
            const examsData = @json($examsByMonth);
            const statusLabels = @json(collect($invoiceStatusStats)->pluck('label')->values());
            const statusCounts = @json(collect($invoiceStatusStats)->pluck('count')->values());

            const gridColor = '#f1f5f9';
            const tickColor = '#94a3b8';

            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(revenueData),
                    datasets: [{
                        data: Object.values(revenueData),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2563eb',
                        borderWidth: 2,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor } },
                        y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: (v) => v.toLocaleString('fr-FR') } },
                    },
                    maintainAspectRatio: false,
                },
            });

            new Chart(document.getElementById('examsChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(examsData),
                    datasets: [{
                        data: Object.values(examsData),
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20, 184, 166, 0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#14b8a6',
                        borderWidth: 2,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor } },
                        y: { grid: { color: gridColor }, ticks: { color: tickColor, precision: 0 } },
                    },
                    maintainAspectRatio: false,
                },
            });

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                        borderWidth: 0,
                        borderRadius: 6,
                        spacing: 2,
                    }],
                },
                options: {
                    cutout: '70%',
                    plugins: { legend: { display: false } },
                    maintainAspectRatio: false,
                },
            });
        });
    </script>
</x-app-layout>