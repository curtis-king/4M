@php $canFin = auth()->user()->can('view financial data'); @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Visite du {{ $visit->visit_date->format('d/m/Y') }}
            </h2>
            @if ($visit->status === 'brouillon' || !$visit->isBilled())
                <a href="{{ route('visits.edit', $visit) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Modifier
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            {{-- Barre de statut de facturation --}}
            @if ($visit->isBilled())
                @php $statement = $visit->statementItems->first()?->invoice; @endphp
                @if ($statement)
                    <div class="bg-green-50 border border-green-200 rounded-2xl px-5 py-4 flex flex-wrap items-center justify-between gap-2">
                        <div class="text-sm text-green-800">
                            <span class="font-semibold">Déjà facturée</span> dans la facture de sommation
                            <a href="{{ route('invoices.show', $statement) }}" class="font-semibold text-green-700 underline">{{ $statement->number }}</a>
                            du {{ $statement->date->format('d/m/Y') }}.
                        </div>
                    </div>
                @endif
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-card rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Société / Assuré</dt>
                            <dd class="text-gray-900 font-medium">{{ $visit->client->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Agents / Employés</dt>
                            <dd class="text-gray-900 text-right">
                                @if ($visit->agents->count() > 1)
                                <span class="inline-block rounded-full bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 mb-1">{{ $visit->agents->count() }} employés</span>
                                @endif
                                <div>
                                @foreach ($visit->agents as $agent)
                                    <div>{{ $agent->name }}{{ $agent->matricule ? ' ('.$agent->matricule.')' : '' }}</div>
                                @endforeach
                                @if ($visit->agents->isEmpty() && $visit->agent)
                                    <div>{{ $visit->agent->name }}</div>
                                @endif
                                @if ($visit->agents->isEmpty() && !$visit->agent)
                                    @if ($visit->beneficiary_name)
                                        <div>{{ $visit->beneficiary_name }} <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-600 text-xs font-medium px-2 py-0.5">non référencé</span></div>
                                    @else
                                        —
                                    @endif
                                @endif
                                </div>
                            </dd>
                        </div>
                        @if ($visit->objet)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Objet</dt>
                            <dd class="text-gray-900">{{ $visit->objet }}</dd>
                        </div>
                        @endif
                        @if ($visit->is_controle_alimentaire)
                        <div class="flex justify-between border-t border-emerald-100 pt-2">
                            <dt class="text-gray-500 font-medium text-emerald-700">Contrôle alimentaire</dt>
                            <dd>
                                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-0.5">Produit : {{ $visit->produit_alimentaire }}</span>
                            </dd>
                        </div>
                        @if ($visit->numero_lot)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">N° de lot</dt>
                            <dd class="text-gray-900">{{ $visit->numero_lot }}</dd>
                        </div>
                        @endif
                        @if ($visit->origine_produit)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Origine</dt>
                            <dd class="text-gray-900">{{ $visit->origine_produit }}</dd>
                        </div>
                        @endif
                        @if ($visit->date_prelevement)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Prélèvement</dt>
                            <dd class="text-gray-900">{{ $visit->date_prelevement->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                        @if ($visit->type_controle)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Type de contrôle</dt>
                            <dd class="text-gray-900 capitalize">{{ $visit->type_controle }}</dd>
                        </div>
                        @endif
                        @if ($visit->statut_resultat)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Résultat</dt>
                            <dd>
                                @if ($visit->statut_resultat === 'conforme')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Conforme</span>
                                @elseif ($visit->statut_resultat === 'non_conforme')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Non conforme</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">En attente</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        @endif
                        @if ($visit->insuranceContract)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Assurance</dt>
                            <dd class="text-gray-900">
                                {{ $visit->insuranceContract->insurer->name ?? 'Assureur' }}
                                — {{ number_format($visit->insuranceContract->coverage_rate, 0, ',', ' ') }}%
                                <span class="text-xs text-gray-400">({{ $visit->insuranceContract->contract_number }})</span>
                            </dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Date</dt>
                            <dd class="text-gray-900">{{ $visit->visit_date->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Statut</dt>
                            <dd>
                                @php
                                    $statusColors = ['planifiee' => 'bg-blue-100 text-blue-800', 'realisee' => 'bg-green-100 text-green-800', 'annulee' => 'bg-red-100 text-red-800', 'absent' => 'bg-yellow-100 text-yellow-800'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$visit->status] ?? '' }}">
                                    {{ ucfirst($visit->status) }}
                                </span>
                            </dd>
                        </div>
                        @if ($visit->notes)
                        <div>
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-900 mt-1">{{ $visit->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                @if ($canFin)
                <div class="bg-white shadow-card rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Montants (FCFA)</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Sous-total</dt>
                            <dd class="text-gray-900 font-medium">{{ number_format($visit->subtotal, 0, ',', ' ') }}</dd>
                        </div>
                        @if ($visit->discount_amount > 0)
                        <div class="flex justify-between text-red-600">
                            <dt>Remise ({{ number_format($visit->discount_value, 0, ',', ' ') }}%)</dt>
                            <dd class="font-medium">- {{ number_format($visit->discount_amount, 0, ',', ' ') }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <dt class="font-semibold">Total</dt>
                            <dd class="font-bold">{{ number_format($visit->total, 0, ',', ' ') }}</dd>
                        </div>
                        @if ($visit->insurance_covered > 0)
                        <div class="flex justify-between text-blue-600">
                            <dt>Part assurance</dt>
                            <dd class="font-medium">{{ number_format($visit->insurance_covered, 0, ',', ' ') }}</dd>
                        </div>
                        <div class="flex justify-between text-orange-600">
                            <dt>Ticket modérateur</dt>
                            <dd class="font-medium">{{ number_format($visit->patient_amount, 0, ',', ' ') }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Reçu du patient</dt>
                            <dd class="text-green-700 font-medium">{{ number_format($visit->patient_paid, 0, ',', ' ') }}</dd>
                        </div>
                    </dl>
                </div>
                @endif
            </div>

            <div class="bg-white shadow-card rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Prestations ({{ $visit->examLines->count() }})</h3>
                @if ($visit->examLines->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prestation</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qté</th>
                                    @if ($canFin)
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Prix unit.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Remise</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($visit->examLines as $line)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <div class="font-medium text-gray-900">{{ $line->service->name ?? 'Prestation' }}</div>
                                        @if ($line->service?->code)
                                            <div class="text-xs text-gray-400">{{ $line->service->code }}</div>
                                        @endif
                                        @if ($line->result)
                                            <div class="text-xs text-gray-500 mt-0.5">Résultat : {{ $line->result }}</div>
                                        @endif
                                        @if ($line->is_alimentaire || $line->resultat_valeur)
                                            <div class="text-xs text-gray-500 mt-0.5 space-x-3">
                                                @if ($line->resultat_valeur)
                                                    <span>Valeur : <strong class="text-gray-700">{{ $line->resultat_valeur }}{{ $line->unite_mesure ? ' '.$line->unite_mesure : '' }}</strong></span>
                                                @endif
                                                @if ($line->valeur_limite)
                                                    <span>Limite : {{ $line->valeur_limite }}</span>
                                                @endif
                                                @if ($line->est_conforme !== null)
                                                    @if ($line->est_conforme)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Conforme</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Non conforme</span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ $line->quantity }}</td>
                                    @if ($canFin)
                                        <td class="px-4 py-2 text-right text-gray-600">{{ number_format($line->effective_price, 0, ',', ' ') }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600">
                                            @if ($line->discount_amount > 0)
                                                -{{ number_format($line->discount_amount, 0, ',', ' ') }} ({{ number_format($line->discount_value, 0, ',', ' ') }}%)
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($line->line_net, 0, ',', ' ') }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Aucune prestation enregistrée.</p>
                @endif
            </div>

            @if ($visit->status === 'realisee' && !$visit->isBilled())
            <div class="flex justify-end">
                <a href="{{ route('invoices.statement.create', ['insurer_id' => $visit->insuranceContract?->insurer_id]) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Facturer dans une sommation
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>