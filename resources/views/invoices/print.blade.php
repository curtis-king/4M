<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }} — {{ $company->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            main { box-shadow: none !important; margin: 0 !important; max-width: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800 min-h-screen py-10">

    <div class="no-print flex justify-center mb-6">
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-md transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0M6.34 18l.229 2.523a1.125 1.125 0 001.12 1.227h8.622a1.125 1.125 0 001.12-1.227L17.66 18M6.34 18H4.75A1.75 1.75 0 013 16.25v-4.875c0-1.036.84-1.875 1.875-1.875h14.25A1.875 1.875 0 0121 11.375v4.875A1.75 1.75 0 0119.25 18H17.66M6.34 18h11.32M6.75 7.5V4.875c0-.621.504-1.125 1.125-1.125h8.25c.621 0 1.125.504 1.125 1.125V7.5"/></svg>
            Imprimer / PDF
        </button>
    </div>

    <main class="max-w-3xl mx-auto bg-white shadow-xl p-10 text-sm text-gray-800">

        {{-- En-tête --}}
        <div class="flex justify-between items-start pb-4 border-b border-gray-300">
            <div>
                @if ($company->logo)
                    <img src="{{ $company->logo }}" alt="Logo {{ $company->name }}" class="h-12 mb-2 object-contain">
                @endif
                <div class="font-bold text-gray-900">{{ $company->name }}</div>
                <div class="text-xs text-gray-500 mt-1 leading-relaxed">
                    {{ $company->address }}<br>
                    Tél. {{ $company->phone }} @if ($company->email) · {{ $company->email }} @endif<br>
                    @if ($company->niu) NIU : {{ $company->niu }} @endif
                    @if ($company->nif) · NIF : {{ $company->nif }} @endif
                    @if ($company->rc) · RC : {{ $company->rc }} @endif
                </div>
            </div>
            <div class="text-right shrink-0 pl-6">
                <div class="text-3xl font-bold text-blue-600 tracking-tight">
                    @if ($invoice->is_statement)
                        FACTURE DE SOMMATION
                    @elseif ($invoice->is_controle_alimentaire)
                        FACTURE — CONTRÔLE ALIMENTAIRE
                    @else
                        FACTURE
                    @endif
                </div>
                <div class="text-xs text-gray-500 mt-2 space-y-0.5">
                    <div>N° facture : <span class="font-medium text-gray-900">{{ $invoice->number }}</span></div>
                    @if ($invoice->voucher_number)
                        <div>N° de bon : <span class="font-medium text-gray-900">{{ $invoice->voucher_number }}</span></div>
                    @endif
                    <div>Date : <span class="font-medium text-gray-900">{{ $invoice->date->format('d/m/Y') }}</span></div>
                    @if ($invoice->due_date)
                        <div>Échéance : <span class="font-medium text-gray-900">{{ $invoice->due_date->format('d/m/Y') }}</span></div>
                    @endif
                    @if ($invoice->is_statement && $invoice->statement_start_date)
                        <div>Période : <span class="font-medium text-gray-900">{{ $invoice->statement_start_date->format('d/m/Y') }} — {{ $invoice->statement_end_date?->format('d/m/Y') }}</span></div>
                    @endif
                    @if ($invoice->pec_number)
                        <div>N° PEC : <span class="font-medium text-gray-900">{{ $invoice->pec_number }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Facturer à --}}
        <div class="mt-6">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Facturer à</div>
            <div class="mt-1 font-bold text-gray-900">{{ $invoice->recipient_name }}</div>
            @if ($invoice->client)
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ $invoice->client->phone }}
                    @if ($invoice->client->email) · {{ $invoice->client->email }} @endif
                </div>
                @if ($invoice->client->address)
                    <div class="text-xs text-gray-500">{{ $invoice->client->address }}</div>
                @endif
                @if ($invoice->client->niu)
                    <div class="text-xs text-gray-500">NIU : {{ $invoice->client->niu }}</div>
                @endif
            @endif
            @if ($invoice->agent)
                <div class="text-xs text-gray-400 mt-1">Agent : {{ $invoice->agent->name }}@if ($invoice->agent->matricule) (Mat. {{ $invoice->agent->matricule }})@endif</div>
            @endif
            @if ($invoice->insuranceContract)
                <div class="text-xs text-blue-600 mt-1">Assurance : {{ $invoice->insuranceContract->insurer->name }} — Couverture : {{ number_format((float) $invoice->insuranceContract->coverage_rate, 0) }}%</div>
            @endif
        </div>

        {{-- Objet de la mission --}}
        @if ($invoice->subject || $invoice->sample_nature || $invoice->company_site)
            <div class="mt-4 grid grid-cols-3 gap-4 text-xs bg-gray-50 rounded-lg p-3">
                <div>
                    <div class="text-gray-400 uppercase text-[10px] tracking-wide">Objet</div>
                    <div class="text-gray-800 mt-0.5">{{ $invoice->subject ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-400 uppercase text-[10px] tracking-wide">Nature des échantillons</div>
                    <div class="text-gray-800 mt-0.5">{{ $invoice->sample_nature ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-400 uppercase text-[10px] tracking-wide">Site de l'entreprise</div>
                    <div class="text-gray-800 mt-0.5">{{ $invoice->company_site ?: '—' }}</div>
                </div>
            </div>
        @endif

        {{-- Lignes de facture --}}
        @if ($invoice->is_statement)
            @include('invoices._statement_print')
        @else
            <table class="w-full mt-6 text-sm">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="text-left font-semibold text-gray-700 pb-2">Désignation</th>
                        <th class="text-center font-semibold text-gray-700 pb-2">Qté</th>
                        <th class="text-right font-semibold text-gray-700 pb-2">Prix unit.</th>
                        <th class="text-right font-semibold text-gray-700 pb-2">Remise</th>
                        <th class="text-right font-semibold text-gray-700 pb-2">Montant HT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="py-2 text-gray-800">
                                {{ $item->description }}
                                @if ($item->service?->code)
                                    <span class="text-gray-400 text-xs">({{ $item->service->code }})</span>
                                @endif
                            </td>
                            <td class="py-2 text-center text-gray-600">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                            <td class="py-2 text-right text-gray-600">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                            <td class="py-2 text-right text-gray-600">{{ $item->discount_amount > 0 ? '-'.number_format($item->discount_amount, 0, ',', ' ') : '—' }}</td>
                            <td class="py-2 text-right font-medium text-gray-900">{{ number_format($item->net_amount, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Totaux --}}
        <div class="flex justify-end mt-4">
            <div class="w-64 text-sm space-y-1.5">
                @if ($invoice->is_statement)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Montant complet</span>
                        <span class="text-gray-900">{{ number_format($invoice->subtotal, 0, ',', ' ') }}</span>
                    </div>
                    <div class="flex justify-between text-blue-600">
                        <span>Part assureur</span>
                        <span>{{ number_format($invoice->insurance_covered, 0, ',', ' ') }}</span>
                    </div>
                @else
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sous-total HT</span>
                        <span class="text-gray-900">{{ number_format($invoice->subtotal, 0, ',', ' ') }}</span>
                    </div>
                    @if ($invoice->discount_amount > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Remise globale</span>
                            <span>- {{ number_format($invoice->discount_amount, 0, ',', ' ') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">TVA ({{ $invoice->tax_rate }}%)</span>
                        <span class="text-gray-900">{{ number_format($invoice->tax_amount, 0, ',', ' ') }}</span>
                    </div>
                @endif

                <div class="flex justify-between pt-2 mt-1 border-t-4 border-double border-black font-bold text-base text-gray-900">
                    <span>{{ $invoice->is_statement ? 'NET À PAYER' : 'TOTAL TTC' }}</span>
                    <span>{{ number_format($invoice->total, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                </div>

                @if (!$invoice->is_statement && $invoice->insurance_covered > 0)
                    <div class="flex justify-between text-blue-600">
                        <span>Assurance ({{ number_format((float) $invoice->insuranceContract?->coverage_rate, 0) }}%)</span>
                        <span>{{ number_format($invoice->insurance_covered, 0, ',', ' ') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-orange-600">
                        <span>Ticket modérateur</span>
                        <span>{{ number_format($invoice->patient_amount, 0, ',', ' ') }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-green-600">
                    <span>Déjà payé</span>
                    <span>{{ number_format($invoice->paid_amount, 0, ',', ' ') }}</span>
                </div>
                <div class="flex justify-between pt-1.5 mt-1 border-t border-gray-300 font-bold text-red-600">
                    <span>Reste à payer</span>
                    <span>{{ number_format($invoice->amountDue, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

        {{-- Certification SFEC --}}
        @if ($invoice->sfec_certified)
            <div class="mt-8 text-center">
                @if ($invoice->sfec_qr_code)
                    <img src="data:image/png;base64,{{ $invoice->sfec_qr_code }}" alt="QR code SFEC" class="w-24 h-24 mx-auto">
                @endif
                @if ($invoice->sfec_short_signature)
                    <div class="text-xs text-gray-400 mt-1">{{ $invoice->sfec_short_signature }}</div>
                @endif
            </div>
        @endif

        {{-- Pied de page --}}
        <div class="mt-8 pt-4 border-t border-gray-200 text-center text-xs text-gray-400 leading-relaxed">
            {{ $company->name }} — {{ $company->address }}<br>
            @if ($company->niu) NIU : {{ $company->niu }} @endif
            @if ($company->nif) · NIF : {{ $company->nif }} @endif
            @if ($company->rc) · RC : {{ $company->rc }} @endif
            @if ($company->bank_name)
                <br>Banque : {{ $company->bank_name }} @if ($company->bank_rib) — RIB : {{ $company->bank_rib }} @endif
            @endif
            <br>Facture générée le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </main>
</body>
</html>
