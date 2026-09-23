<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div class="leading-tight">
                <h1 class="text-base font-semibold text-gray-900 sm:text-lg">
                    Trésorerie &amp; facturation
                </h1>
                <p class="text-xs text-gray-400 sm:text-sm">
                    {{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('import financial data')
                <a href="{{ route('finance.imports') }}" class="inline-flex items-center gap-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 text-sm font-medium px-3 py-2 rounded-lg transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
                    Import Excel
                </a>
                @endcan
                @can('export financial data')
                <a href="{{ route('finance.exports') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Export Sage
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">

        {{-- KPI trésorerie --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6l9.75 6.75L21.75 6M2.25 6v12a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V6m-19.5 0a1.5 1.5 0 011.5-1.5h16.5a1.5 1.5 0 011.5 1.5" />
                    </svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($caMois, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                <div class="mt-1 text-sm text-gray-500">CA du mois</div>
                @if (!is_null($caEvolutionPct))
                    <span class="mt-1 inline-flex items-center gap-1 text-xs font-medium {{ $caEvolutionPct >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $caEvolutionPct >= 0 ? '▲' : '▼' }} {{ abs($caEvolutionPct) }}% vs mois précédent
                    </span>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($totalEncaisse, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                <div class="mt-1 text-sm text-gray-500">Total encaissé</div>
                <div class="mt-1 text-xs text-gray-400">dont {{ number_format($caPaye, 0, ',', ' ') }} payées</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                    </svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($totalRestant, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                <div class="mt-1 text-sm text-gray-500">Reste à encaisser</div>
                <div class="mt-1 text-xs text-rose-600">dont {{ number_format($totalEchu, 0, ',', ' ') }} échus</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-5 0a3 3 0 110 6m9-3a3 3 0 11-3 3m0-3a3 3 0 013-3m-3 3a3 3 0 013 3M6 12a3 3 0 11-3 3m3-3a3 3 0 013 3" />
                    </svg>
                </div>
                <div class="mt-4 text-2xl font-bold text-gray-900">{{ $nbDevisAConvertir }}</div>
                <div class="mt-1 text-sm text-gray-500">Devis acceptés à facturer</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- Répartition des factures --}}
            <div class="rounded-2xl bg-white p-6 shadow-card">
                <h3 class="text-sm font-semibold text-gray-800">Répartition des factures</h3>
                <p class="text-xs text-gray-400">Par statut de paiement</p>
                <div class="mt-4 space-y-3">
                    @foreach ($invoiceStatusStats as $key => $stat)
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2 w-2 rounded-full {{ $key === 'payee' ? 'bg-emerald-500' : ($key === 'partiel' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ $stat['label'] }}
                            </span>
                            <span class="font-medium text-gray-900">{{ $stat['count'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-400 pl-4 -mt-2">
                            <span>&nbsp;</span>
                            <span>{{ number_format((float) $stat['total'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Devis acceptés à convertir --}}
            <div class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-800">Devis acceptés à facturer</h3>
                    <a href="{{ route('devis.index', ['status' => 'accepte']) }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir</a>
                </div>
                @if ($devisAEteindre->count())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($devisAEteindre as $devis)
                            <li class="flex items-center gap-3 px-6 py-3">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('devis.show', $devis) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-primary-600">{{ $devis->number }}</a>
                                    <span class="text-xs text-gray-400">{{ $devis->recipient_name }}</span>
                                </div>
                                <span class="shrink-0 text-sm font-medium text-gray-900">{{ number_format($devis->total, 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-6 py-8 text-center text-sm text-gray-400">Aucun devis accepté en attente.</p>
                @endif
            </div>

            {{-- Derniers encaissements --}}
            <div class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-800">Derniers encaissements</h3>
                    <a href="{{ route('payments.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
                </div>
                @if ($paymentsRecents->count())
                    <ul class="divide-y divide-gray-100">
                        @php
                            $methodLabels = [
                                'especes' => 'Espèces',
                                'virement' => 'Virement',
                                'mobile_money' => 'Mobile Money',
                                'cheque' => 'Chèque',
                                'carte' => 'Carte',
                            ];
                        @endphp
                        @foreach ($paymentsRecents as $payment)
                            <li class="flex items-center gap-3 px-6 py-3">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-primary-600">{{ $payment->invoice->number }}</a>
                                    <span class="text-xs text-gray-400">{{ $payment->payment_date->format('d/m/Y') }} · {{ $methodLabels[$payment->method] ?? $payment->method }}</span>
                                </div>
                                <span class="shrink-0 text-sm font-semibold text-emerald-600">+ {{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-6 py-8 text-center text-sm text-gray-400">Aucun encaissement.</p>
                @endif
            </div>
        </div>

        {{-- Pipeline devis --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            @include('dashboard.partials.devis-pipeline')
            <div class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-800">Dernières factures</h3>
                    <a href="{{ route('invoices.index') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Voir tout</a>
                </div>
                @if ($facturesRecents->count())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($facturesRecents as $invoice)
                            <li class="flex items-center gap-3 px-6 py-3">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-primary-600">{{ $invoice->number }}</a>
                                    <span class="text-xs text-gray-400">{{ $invoice->recipient_name }} · {{ $invoice->date->format('d/m/Y') }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $invoice->status_badge }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                                <span class="shrink-0 text-sm font-medium text-gray-900">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-6 py-8 text-center text-sm text-gray-400">Aucune facture.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>