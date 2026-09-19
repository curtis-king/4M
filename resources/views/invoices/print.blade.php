<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }} — {{ $company->name }}</title>
    <style>
        @page { size: A4; margin: 11mm; } * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font: 10px/1.35 Arial, Helvetica, sans-serif; }
        .sheet { width: 100%; max-width: 190mm; margin: 0 auto; } .toolbar { padding: 16px; text-align: center; background: #f3f4f6; }
        .toolbar button { border: 0; border-radius: 6px; padding: 9px 18px; color: #fff; background: #0f766e; font-weight: bold; cursor: pointer; }
        .brand { display: grid; grid-template-columns: 125px 1fr 145px; gap: 12px; align-items: center; border-bottom: 2px solid #14532d; padding: 0 0 10px; }
        .logo { max-height: 55px; max-width: 115px; object-fit: contain; } .company-name { margin: 0 0 3px; color: #14532d; font-size: 16px; letter-spacing: .2px; text-transform: uppercase; }
        .company-details { color: #4b5563; font-size: 8.5px; } .document-title { border: 1px solid #14532d; text-align: center; padding: 8px 6px; color: #14532d; font-size: 14px; font-weight: 800; }
        .document-title small { display: block; margin-top: 3px; color: #374151; font-size: 8px; font-weight: normal; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; margin-top: 10px; border: 1px solid #94a3b8; } .meta > div { padding: 7px 9px; } .meta > div + div { border-left: 1px solid #94a3b8; }
        .label { display: block; color: #475569; font-size: 8px; font-weight: bold; letter-spacing: .4px; text-transform: uppercase; } .value { display: block; margin-top: 2px; font-size: 10px; font-weight: 700; }
        .info-grid { display: grid; grid-template-columns: 1.25fr 1fr; gap: 10px; margin: 10px 0; } .box { border: 1px solid #94a3b8; } .box-title { padding: 5px 8px; color: #fff; background: #14532d; font-size: 8px; font-weight: bold; letter-spacing: .4px; text-transform: uppercase; }
        .box-content { min-height: 52px; padding: 7px 8px; } .recipient { font-size: 11px; font-weight: bold; } .muted { color: #64748b; }
        .mission-line { margin: 8px 0; padding: 6px 9px; border: 1px solid #94a3b8; font-size: 9.5px; color: #334155; }
        .mission-line .label { display: inline; color: #14532d; font-size: 8px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; } .items { margin-top: 10px; border: 1px solid #64748b; } .items th { padding: 6px 5px; color: #fff; background: #14532d; border: 1px solid #64748b; font-size: 8px; letter-spacing: .25px; text-align: left; text-transform: uppercase; }
        .items td { padding: 6px 5px; border: 1px solid #cbd5e1; vertical-align: top; } .items .center { text-align: center; } .items .right { text-align: right; white-space: nowrap; } .items tbody tr:nth-child(even) { background: #f8fafc; }
        .items tfoot td { padding: 6px 5px; background: #f1f5f9; border-top: 1px solid #64748b; font-weight: 600; }
        .statement-company { margin-top: 12px; } .statement-company-title { margin-bottom: 4px; color: #14532d; font-size: 10px; font-weight: bold; letter-spacing: .4px; text-transform: uppercase; } .statement-company-title .muted { font-weight: normal; }
        .company-list { display: block; } .company-list .label { margin-top: 4px; } .company-list .company-name { display: inline-block; margin: 2px 8px 2px 0; padding: 1px 6px; color: #14532d; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .totals { width: 250px; margin: 12px 0 0 auto; border: 1px solid #64748b; } .totals td { padding: 4px 7px; border-bottom: 1px solid #cbd5e1; } .totals td:last-child { text-align: right; font-weight: bold; }
        .totals .due td { color: #fff; background: #14532d; border: 0; font-size: 11px; font-weight: bold; } .footer { display: grid; grid-template-columns: 1fr 150px; gap: 14px; align-items: end; margin-top: 18px; border-top: 1px solid #94a3b8; padding-top: 8px; color: #475569; font-size: 8px; }
        .payment { border: 1px solid #94a3b8; padding: 6px; } .signature { height: 45px; padding-top: 5px; border-top: 1px solid #94a3b8; text-align: center; }
        .sfec { display: grid; grid-template-columns: 96px 1fr; gap: 12px; align-items: center; margin-top: 14px; border: 1px solid #14532d; background: #f8faf5; padding: 10px 12px; }
        .sfec-qr { width: 88px; height: 88px; display: flex; align-items: center; justify-content: center; padding: 3px; background: #fff; border: 1px solid #cbd5e1; }
        .sfec-qr img { width: 100%; height: 100%; object-fit: contain; }
        .sfec-title { color: #14532d; font-size: 10px; font-weight: bold; letter-spacing: .4px; text-transform: uppercase; }
        .sfec-info { color: #334155; font-size: 9px; line-height: 1.55; }
        .sfec-info .v { font-weight: bold; color: #0f172a; }
        @media print { .toolbar { display: none; } .sheet { max-width: none; } }
    </style>
</head>
<body>
    @php $statementGroups = $invoice->items->groupBy(fn ($item) => $item->company_name ?: 'Divers'); @endphp
    <div class="toolbar"><button onclick="window.print()">Imprimer / Enregistrer en PDF</button></div>
    <main class="sheet">
        <header class="brand">
            <div>@if ($company->logo)<img src="{{ $company->logo }}" alt="Logo {{ $company->name }}" class="logo">@endif</div>
            <div><h1 class="company-name">{{ $company->name }}</h1><div class="company-details">{{ $company->address }}<br>Tél. {{ $company->phone }} @if($company->email) · {{ $company->email }} @endif<br>@if($company->niu) NIU : {{ $company->niu }} @endif @if($company->nif) · NIF : {{ $company->nif }} @endif @if($company->rc) · RC : {{ $company->rc }} @endif</div></div>
            <div class="document-title">@if ($invoice->is_statement) FACTURE DE SOMMATION @elseif ($invoice->is_controle_alimentaire) FACTURE — CONTRÔLE ALIMENTAIRE @else FACTURE @endif<small>{{ $invoice->number }}</small></div>
        </header>
        <section class="meta"><div><span class="label">Date</span><span class="value">{{ $invoice->date->format('d/m/Y') }}</span></div><div><span class="label">Bon / Référence</span><span class="value">{{ $invoice->voucher_number ?: '—' }}</span></div></section>
        <section class="info-grid">
            <div class="box"><div class="box-title">Client / entreprise</div><div class="box-content"><div class="recipient">{{ $invoice->recipient_name }}</div>@if ($invoice->client)<div class="muted">{{ $invoice->client->address ?: $invoice->client->city }}</div><div class="muted">{{ $invoice->client->phone }} @if($invoice->client->email) · {{ $invoice->client->email }} @endif</div>@endif @if ($invoice->company_site)<div class="muted">Site : {{ $invoice->site->name ?? $invoice->company_site }}</div>@endif</div></div>
            <div class="box"><div class="box-title">Références de facturation</div><div class="box-content"><span class="label">N° facture</span><span class="value">{{ $invoice->number }}</span>@if ($invoice->pec_number)<span class="muted">PEC : {{ $invoice->pec_number }}</span>@endif @if ($invoice->due_date)<br><span class="muted">Échéance : {{ $invoice->due_date->format('d/m/Y') }}</span>@endif</div></div>
        </section>
        @if ($invoice->is_statement)
            <section class="box">
                <div class="box-title">Objet / prestation</div>
                <div class="box-content company-list">
                    <span class="label">Période</span><span class="value">{{ $invoice->statement_start_date?->format('d/m/Y') }} — {{ $invoice->statement_end_date?->format('d/m/Y') }}</span>
                    @foreach ($statementGroups as $entreprise => $entrepriseItems)<span class="company-name">{{ $entreprise }} · {{ $entrepriseItems->count() }}</span>@endforeach
                </div>
            </section>
        @endif
        @if ($invoice->subject || $invoice->sample_nature)
            <div class="mission-line">
                @if ($invoice->subject)<span class="label">Objet :</span> {{ $invoice->subject }}@endif
                @if ($invoice->sample_nature)&nbsp;&nbsp;·&nbsp;&nbsp;<span class="label">Échantillons :</span> {{ $invoice->sample_nature }}@endif
            </div>
        @endif
        @if ($invoice->is_statement)
            @include('invoices._statement_print')
        @else
            <table class="items"><thead><tr><th>Désignation</th><th class="center">Qté</th><th class="right">P.U.</th><th class="right">Remise</th><th class="right">P.T.</th></tr></thead><tbody>@foreach ($invoice->items as $item)<tr><td>{{ $item->description }} @if($item->service?->code)<span class="muted">({{ $item->service->code }})</span>@endif</td><td class="center">{{ number_format($item->quantity, 0, ',', ' ') }}</td><td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td><td class="right">{{ $item->discount_amount > 0 ? '- '.number_format($item->discount_amount, 0, ',', ' ') : '—' }}</td><td class="right"><strong>{{ number_format($item->net_amount, 0, ',', ' ') }}</strong></td></tr>@endforeach</tbody></table>
        @endif
        <table class="totals">
            @if ($invoice->is_statement)<tr><td>Montant complet</td><td>{{ number_format($invoice->subtotal, 0, ',', ' ') }}</td></tr><tr><td>Part assureur</td><td>{{ number_format($invoice->insurance_covered, 0, ',', ' ') }}</td></tr>
            @else<tr><td>Sous-total HT</td><td>{{ number_format($invoice->subtotal, 0, ',', ' ') }}</td></tr>@if ($invoice->discount_amount > 0)<tr><td>Remise</td><td>- {{ number_format($invoice->discount_amount, 0, ',', ' ') }}</td></tr>@endif<tr><td>TVA ({{ $invoice->tax_rate }}%)</td><td>{{ number_format($invoice->tax_amount, 0, ',', ' ') }}</td></tr>@endif
            <tr class="due"><td>{{ $invoice->is_statement ? 'NET À PAYER' : 'TOTAL TTC' }}</td><td>{{ number_format($invoice->total, 0, ',', ' ') }} {{ $invoice->currency }}</td></tr>
        </table>
        @if ($invoice->sfec_certified)
            <section class="sfec">
                <div class="sfec-qr">@if ($invoice->sfec_qr_code)<img src="data:image/png;base64,{{ $invoice->sfec_qr_code }}" alt="QR code SFEC">@else<span class="muted">QR indisponible</span>@endif</div>
                <div class="sfec-info">
                    <div class="sfec-title">Facture certifiée SFEC</div>
                    <div>N° de certification : <span class="v">{{ $invoice->sfec_certification_number }}</span></div>
                    @if ($invoice->sfec_short_signature)<div>Signature : <span class="v">{{ $invoice->sfec_short_signature }}</span></div>@endif
                    <div>Date et heure de certification : <span class="v">{{ $invoice->sfec_certification_date?->format('d/m/Y H:i') }}</span></div>
                </div>
            </section>
        @endif
        <footer class="footer"><div><div class="payment"><strong>Mode de paiement :</strong> Espèces, chèque ou virement bancaire.@if($company->bank_name) Banque : {{ $company->bank_name }} @if($company->bank_rib) — RIB : {{ $company->bank_rib }} @endif @endif</div>@if($invoice->notes)<div style="margin-top:5px;"><strong>Observations :</strong> {{ $invoice->notes }}</div>@endif</div><div class="signature">Le service comptabilité<br><br>Signature et cachet</div></footer>
    </main>
</body>
</html>
