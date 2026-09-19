<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                @if ($invoice->is_statement)
                    Facture de sommation {{ $invoice->number }}
                @elseif ($invoice->is_controle_alimentaire)
                    Facture Contrôle Alimentaire {{ $invoice->number }}
                @else
                    Facture {{ $invoice->number }}
                @endif
                @if ($invoice->sfec_certified)
                    <span class="ml-2 text-sm text-green-600 font-normal">✓ Certifiée SFEC</span>
                @endif
            </h2>
            <div class="flex gap-2">
                @if ($invoice->status !== 'annulee')
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0M6.34 18l.229 2.523a1.125 1.125 0 001.12 1.227h8.622a1.125 1.125 0 001.12-1.227L17.66 18M6.34 18H4.75A1.75 1.75 0 013 16.25v-4.875c0-1.036.84-1.875 1.875-1.875h14.25A1.875 1.875 0 0121 11.375v4.875A1.75 1.75 0 0119.25 18H17.66M6.34 18h11.32M6.75 7.5V4.875c0-.621.504-1.125 1.125-1.125h8.25c.621 0 1.125.504 1.125 1.125V7.5"/></svg>
                        Imprimer
                    </a>
                @endif
                @if ($invoice->status === 'brouillon' && !$invoice->is_statement)
                    <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        Modifier
                    </a>
                @endif
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

            {{-- Résumé des montants --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h18a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-18a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($invoice->total, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">{{ $invoice->currency }}</span></div>
                    <div class="mt-1 text-sm text-gray-500">{{ $invoice->is_statement ? 'Net à payer' : 'Total TTC' }}</div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-gray-900">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">{{ $invoice->currency }}</span></div>
                    <div class="mt-1 text-sm text-gray-500">Déjà payé</div>
                    <div class="mt-1 text-xs text-gray-400">{{ $payments->count() }} paiement(s)</div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $invoice->amountDue > 0 ? 'bg-amber-50 text-amber-600' : 'bg-gray-50 text-gray-400' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-2xl font-bold {{ $invoice->amountDue > 0 ? 'text-rose-600' : 'text-gray-400' }}">{{ number_format($invoice->amountDue, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">{{ $invoice->currency }}</span></div>
                    <div class="mt-1 text-sm text-gray-500">Reste à payer</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Sidebar infos --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Statut</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_badge }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </dd>
                            </div>
                            @if ($invoice->is_controle_alimentaire)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Type</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Contrôle Alimentaire</span>
                                </dd>
                            </div>
                            @endif
                            @if ($invoice->subject)
                            <div class="flex justify-between border-t border-gray-100 pt-3">
                                <dt class="text-gray-500">Objet</dt>
                                <dd class="text-gray-900 text-right ml-4">{{ $invoice->subject }}</dd>
                            </div>
                            @endif
                            @if ($invoice->sample_nature)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Nature des échantillons</dt>
                                <dd class="text-gray-900 text-right ml-4">{{ $invoice->sample_nature }}</dd>
                            </div>
                            @endif
                            @if ($invoice->is_statement)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Période</dt>
                                <dd class="text-gray-900">
                                    {{ $invoice->statement_start_date?->format('d/m/Y') }} — {{ $invoice->statement_end_date?->format('d/m/Y') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Visites</dt>
                                <dd class="text-gray-900">{{ $invoice->items->count() }} line(s)</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Date</dt>
                                <dd class="text-gray-900">{{ $invoice->date->format('d/m/Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Échéance</dt>
                                <dd class="text-gray-900">{{ $invoice->due_date->format('d/m/Y') }}</dd>
                            </div>
                            @if ($invoice->voucher_number)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">N° Bon</dt>
                                <dd class="text-gray-900">{{ $invoice->voucher_number }}</dd>
                            </div>
                            @endif
                            @if ($invoice->pec_number)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">N° PEC</dt>
                                <dd class="text-gray-900">{{ $invoice->pec_number }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Créé par</dt>
                                <dd class="text-gray-900">{{ $invoice->creator->name ?? '—' }}</dd>
                            </div>
                            @if ($invoice->sfec_certified)
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">N° SFEC</dt>
                                    <dd class="text-green-700 text-xs">{{ $invoice->sfec_certification_number }}</dd>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <dt class="text-gray-500">Date certif.</dt>
                                    <dd class="text-gray-900">{{ $invoice->sfec_certification_date?->format('d/m/Y H:i') }}</dd>
                                </div>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            @if ($invoice->is_statement) Assureur @else Client @endif
                        </h3>
                        <dl class="space-y-2 text-sm">
                            @if ($invoice->client)
                                <div>
                                    <a href="{{ route('clients.show', $invoice->client) }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $invoice->client->name }}
                                    </a>
                                </div>
                                <div class="text-gray-600">{{ $invoice->client->phone }}</div>
                                @if ($invoice->client->email)
                                    <div class="text-gray-600">{{ $invoice->client->email }}</div>
                                @endif
                            @else
                                <div class="font-medium text-gray-900">{{ $invoice->recipient_name }}</div>
                                <div class="text-xs text-gray-400">Client de passage (sans fiche)</div>
                            @endif
                            @if ($invoice->company_site)
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="text-xs text-gray-400">Site</div>
                                    <div class="text-gray-900">{{ $invoice->company_site }}</div>
                                </div>
                            @endif
                            @if ($invoice->agent)
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="text-xs text-gray-400">Agent</div>
                                    <div class="text-gray-900">{{ $invoice->agent->name }}</div>
                                </div>
                            @endif
                            @if ($invoice->insuranceContract)
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="text-xs text-gray-400">Assurance</div>
                                    <div class="text-gray-900">{{ $invoice->insuranceContract->insurer->name }} — {{ $invoice->insuranceContract->coverage_rate }}%</div>
                                </div>
                            @endif
                            @if ($invoice->is_statement)
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="text-xs text-gray-400">Type</div>
                                    <div class="text-purple-700 font-medium">Facture de sommation mensuelle</div>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Actions statut --}}
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Actions</h3>
                        <div class="space-y-2">
                            @if ($invoice->status !== 'payee' && $invoice->status !== 'annulee')
                            <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                                @csrf @method('PATCH')
                                <div class="flex gap-2">
                                    <select name="status" class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
                                        <option value="brouillon">Brouillon</option>
                                        <option value="envoyee">Envoyée</option>
                                        <option value="annulee">Annulée</option>
                                    </select>
                                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-3 py-1.5 rounded-lg">OK</button>
                                </div>
                            </form>
                            @endif

                            @if (!$invoice->sfec_certified && !in_array($invoice->status, ['brouillon', 'annulee']))
                                @if ($payments->count() > 0)
                                <form method="POST" action="{{ route('invoices.certify', $invoice) }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
                                        onclick="return confirm('Certifier cette facture SFEC ?')">
                                        Certifier SFEC
                                    </button>
                                </form>
                                @else
                                <p class="text-xs text-gray-400 text-center">Enregistrez un paiement pour pouvoir certifier.</p>
                                @endif
                            @endif

                            @if (in_array($invoice->status, ['brouillon', 'annulee']))
                            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
                                    onclick="return confirm('Supprimer cette facture ?')">
                                    Supprimer
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Contenu principal --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Lignes de facture --}}
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            @if ($invoice->is_statement)
                                Détail des visites facturées
                            @else
                                Lignes de facture
                            @endif
                        </h3>

                        @if ($invoice->is_statement)
                            @include('invoices._statement_tables')
                        @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qté</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Prix unit.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Remise</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net HT</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($invoice->items as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 font-medium text-gray-900">
                                            {{ $item->description }}
                                            @if ($item->service)
                                                <div class="text-xs text-gray-400">{{ $item->service->code }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ ucfirst($item->type) }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600">
                                            @if ($item->discount_amount > 0)
                                                -{{ number_format($item->discount_amount, 0, ',', ' ') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($item->net_amount, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <div class="mt-4 flex justify-end">
                            <div class="w-72 space-y-1 text-sm">
                                @if ($invoice->is_statement)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Montant complet (visites) :</span>
                                    <span class="font-medium">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between text-blue-600">
                                    <span>Part assureur (base) :</span>
                                    <span class="font-medium">{{ number_format($invoice->insurance_covered, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @else
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Sous-total HT :</span>
                                    <span class="font-medium">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endif
                                @if ($invoice->discount_amount > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>Remise :</span>
                                    <span class="font-medium">- {{ number_format($invoice->discount_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endif
                                @if (!$invoice->is_statement)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">TVA ({{ $invoice->tax_rate }}%) :</span>
                                    <span class="font-medium">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endif
                                <div class="flex justify-between border-t border-gray-300 pt-1">
                                    @if ($invoice->is_statement)
                                        <span class="font-semibold">Net à payer :</span>
                                    @else
                                        <span class="font-semibold">Total TTC :</span>
                                    @endif
                                    <span class="font-bold text-lg">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @if (!$invoice->is_statement && $invoice->insurance_covered > 0)
                                <div class="flex justify-between text-blue-600">
                                    <span>Assurance :</span>
                                    <span class="font-medium">{{ number_format($invoice->insurance_covered, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between text-orange-600">
                                    <span class="font-semibold">Ticket modérateur :</span>
                                    <span class="font-bold">{{ number_format($invoice->patient_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endif
                                <div class="flex justify-between text-green-600 border-t border-gray-300 pt-1">
                                    <span>Déjà payé :</span>
                                    <span class="font-medium">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between {{ $invoice->amountDue > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    <span class="font-semibold">Reste à payer :</span>
                                    <span class="font-bold">{{ number_format($invoice->amountDue, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Paiements --}}
                    <div class="bg-white shadow-card rounded-2xl p-6" x-data="{ open: false, payer: 'assurance' }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Paiements ({{ $payments->count() }})</h3>
                            @if ($invoice->amountDue > 0 && !in_array($invoice->status, ['brouillon', 'annulee']))
                            <button @click="open = !open"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition">
                                + Enregistrer un paiement
                            </button>
                            @endif
                        </div>

                        {{-- Formulaire paiement --}}
                        <div x-show="open" x-cloak class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <form method="POST" action="{{ route('payments.store', $invoice) }}" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Montant *</label>
                                        <input type="text" name="amount" inputmode="decimal" placeholder="Ex : 1500 ou 1500,50" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">
                                            <span x-show="payer === 'assurance'">Reste : {{ number_format($invoice->insuranceRemaining > 0 ? $invoice->insuranceRemaining : $invoice->amountDue, 0, ',', ' ') }} FCFA</span>
                                            <span x-show="payer === 'patient'">Reste patient : {{ number_format($invoice->patientRemaining, 0, ',', ' ') }} FCFA</span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Date *</label>
                                        <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Moyen *</label>
                                        <select name="method" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            <option value="especes">Espèces</option>
                                            <option value="virement">Virement</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="cheque">Chèque</option>
                                            <option value="carte">Carte</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Payé par *</label>
                                        <select name="payer" x-model="payer" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            @if ($invoice->is_statement)
                                                <option value="assurance">Assurance</option>
                                            @else
                                                <option value="patient">Patient</option>
                                            @endif
                                            @if ($invoice->insurance_covered > 0 && !$invoice->is_statement)
                                                <option value="assurance">Assurance</option>
                                            @endif
                                            @if ($invoice->is_statement)
                                                <option value="patient">Patient</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Référence</label>
                                        <input type="text" name="reference" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
                                        <input type="text" name="notes" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-1.5 rounded-lg">Enregistrer</button>
                                    <button type="button" @click="open = false" class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1.5">Annuler</button>
                                </div>
                            </form>
                        </div>

                        @if ($payments->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Moyen</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payé par</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($payments as $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-600">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ ucfirst($payment->payer) }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $payment->reference ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right font-medium text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                        <td class="px-4 py-2 text-right">
                                            <form method="POST" action="{{ route('payments.destroy', [$invoice, $payment]) }}" class="inline"
                                                onsubmit="return confirm('Supprimer ce paiement ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-sm text-gray-500">Aucun paiement enregistré.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
