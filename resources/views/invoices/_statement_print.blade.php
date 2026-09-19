@php
    $statementGroups = $invoice->items->groupBy(fn ($item) => $item->company_name ?: 'Divers');
@endphp

@foreach ($statementGroups as $company => $items)
    <div class="statement-company">
        <div class="statement-company-title">{{ $company }} <span class="muted">· {{ $items->count() }} prestation{{ $items->count() > 1 ? 's' : '' }}</span></div>
        <table class="items">
            <thead>
                <tr>
                    <th>Assuré</th>
                    <th>Objet / prestation</th>
                    <th class="right">Montant complet</th>
                    <th class="center">Couv.</th>
                    <th class="right">Part assureur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->assured_name ?: '—' }}</td>
                        <td>
                            @php
                                $sourceVisit = $item->sourceVisit;
                                $designations = $sourceVisit?->examLines?->filter(fn ($l) => $l->service?->name);
                            @endphp
                            @if ($sourceVisit?->objet)
                                <strong>{{ $sourceVisit->objet }}</strong><br>
                            @endif
                            @if ($designations && $designations->isNotEmpty())
                                @foreach ($designations as $line)
                                    {{ $line->service->name }} @if ($line->service->code)<span class="muted">({{ $line->service->code }})</span>@endif @if ((float) $line->quantity > 1)<span class="muted">× {{ number_format((float) $line->quantity, 0, ',', ' ') }}</span>@endif<br>
                                @endforeach
                            @else
                                {{ $item->description }}<br>
                            @endif
                            @if ($sourceVisit?->id && $sourceVisit->visit_date)
                                <span class="muted">{{ $sourceVisit->visit_date->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="right">{{ number_format($item->net_amount, 0, ',', ' ') }}</td>
                        <td class="center">{{ $item->coverage_rate !== null ? number_format((float) $item->coverage_rate, 0, ',', ' ').'%' : '—' }}</td>
                        <td class="right"><strong>{{ number_format($item->insurance_part, 0, ',', ' ') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Sous-total {{ $company }}</td>
                    <td class="right">{{ number_format($items->sum('net_amount'), 0, ',', ' ') }}</td>
                    <td></td>
                    <td class="right">{{ number_format($items->sum('insurance_part'), 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endforeach