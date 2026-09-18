<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->number }} — {{ $company->name }}</title>
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
        table { width: 100%; border-collapse: collapse; } .items { margin-top: 10px; border: 1px solid #64748b; } .items th { padding: 6px 5px; color: #fff; background: #14532d; border: 1px solid #64748b; font-size: 8px; letter-spacing: .25px; text-align: left; text-transform: uppercase; }
        .items td { padding: 6px 5px; border: 1px solid #cbd5e1; vertical-align: top; } .items .center { text-align: center; } .items .right { text-align: right; white-space: nowrap; } .items tbody tr:nth-child(even) { background: #f8fafc; }
        .totals { width: 250px; margin: 12px 0 0 auto; border: 1px solid #64748b; } .totals td { padding: 4px 7px; border-bottom: 1px solid #cbd5e1; } .totals td:last-child { text-align: right; font-weight: bold; }
        .totals .due td { color: #fff; background: #14532d; border: 0; font-size: 11px; font-weight: bold; } .footer { display: grid; grid-template-columns: 1fr 150px; gap: 14px; align-items: end; margin-top: 18px; border-top: 1px solid #94a3b8; padding-top: 8px; color: #475569; font-size: 8px; }
        .notes { border: 1px solid #94a3b8; padding: 6px; } .signature { height: 45px; padding-top: 5px; border-top: 1px solid #94a3b8; text-align: center; }
        .validity { margin-top: 10px; border: 1px solid #14532d; background: #f8faf5; padding: 6px 10px; color: #14532d; font-size: 9px; font-weight: bold; }
        @media print { .toolbar { display: none; } .sheet { max-width: none; } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Imprimer / Enregistrer en PDF</button></div>
    <main class="sheet">
        <header class="brand">
            <div>@if ($company->logo)<img src="{{ $company->logo }}" alt="Logo {{ $company->name }}" class="logo">@endif</div>
            <div><h1 class="company-name">{{ $company->name }}</h1><div class="company-details">{{ $company->address }}<br>Tél. {{ $company->phone }} @if($company->email) · {{ $company->email }} @endif<br>@if($company->niu) NIU : {{ $company->niu }} @endif @if($company->nif) · NIF : {{ $company->nif }} @endif @if($company->rc) · RC : {{ $company->rc }} @endif</div></div>
            <div class="document-title">DEVIS<small>{{ $devis->number }}</small></div>
        </header>
        <section class="meta"><div><span class="label">Date</span><span class="value">{{ $devis->date->format('d/m/Y') }}</span></div><div><span class="label">Valide jusqu'au</span><span class="value">{{ $devis->due_date->format('d/m/Y') }}</span></div></section>
        <section class="info-grid">
            <div class="box"><div class="box-title">Client / entreprise</div><div class="box-content"><div class="recipient">{{ $devis->recipient_name }}</div>@if ($devis->client)<div class="muted">{{ $devis->client->address ?: $devis->client->city }}</div><div class="muted">{{ $devis->client->phone }} @if($devis->client->email) · {{ $devis->client->email }} @endif</div>@endif @if ($devis->agent)<div class="muted" style="margin-top:3px;">Agent : {{ $devis->agent->name }}</div>@endif</div></div>
            <div class="box"><div class="box-title">Références</div><div class="box-content"><span class="label">N° devis</span><span class="value">{{ $devis->number }}</span>@if ($devis->due_date)<br><span class="muted">Valable jusqu'au {{ $devis->due_date->format('d/m/Y') }}</span>@endif</div></div>
        </section>
        <table class="items"><thead><tr><th>Désignation</th><th class="center">Qté</th><th class="right">P.U.</th><th class="right">Remise</th><th class="right">P.T.</th></tr></thead><tbody>@foreach ($devis->items as $item)<tr><td>{{ $item->description }} @if($item->service?->code)<span class="muted">({{ $item->service->code }})</span>@endif</td><td class="center">{{ number_format($item->quantity, 0, ',', ' ') }}</td><td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td><td class="right">{{ $item->discount_amount > 0 ? '- '.number_format($item->discount_amount, 0, ',', ' ') : '—' }}</td><td class="right"><strong>{{ number_format($item->net_amount, 0, ',', ' ') }}</strong></td></tr>@endforeach</tbody></table>
        <table class="totals">
            <tr><td>Sous-total HT</td><td>{{ number_format($devis->subtotal, 0, ',', ' ') }}</td></tr>
            @if ($devis->discount_amount > 0)<tr><td>Remise</td><td>- {{ number_format($devis->discount_amount, 0, ',', ' ') }}</td></tr>@endif
            <tr><td>TVA ({{ $devis->tax_rate }}%)</td><td>{{ number_format($devis->tax_amount, 0, ',', ' ') }}</td></tr>
            <tr class="due"><td>TOTAL TTC</td><td>{{ number_format($devis->total, 0, ',', ' ') }} {{ $devis->currency }}</td></tr>
        </table>
        <div class="validity">Ce devis est valable jusqu'au {{ $devis->due_date->format('d/m/Y') }}.</div>
        <footer class="footer"><div>@if($devis->notes)<div class="notes"><strong>Observations :</strong> {{ $devis->notes }}</div>@endif</div><div class="signature">Le laboratoire<br><br>Signature et cachet</div></footer>
    </main>
</body>
</html>