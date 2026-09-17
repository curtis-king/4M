@php
    $statementGroups = $invoice->items->groupBy(fn ($item) => $item->company_name ?: 'Divers');
@endphp

@foreach ($statementGroups as $company => $items)
    <div class="mt-6">
        <div class="text-xs font-semibold text-gray-500 mb-2">
            {{ $company }} <span class="text-gray-400 font-normal">· {{ $items->count() }} prestation{{ $items->count() > 1 ? 's' : '' }}</span>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-black">
                    <th class="text-left font-semibold text-gray-700 pb-2">Assuré</th>
                    <th class="text-left font-semibold text-gray-700 pb-2">Objet / prestation</th>
                    <th class="text-right font-semibold text-gray-700 pb-2">Montant complet</th>
                    <th class="text-center font-semibold text-gray-700 pb-2">Couv.</th>
                    <th class="text-right font-semibold text-gray-700 pb-2">Part assureur</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($items as $item)
                    @php
                        $sourceVisit = $item->sourceVisit;
                        $designations = $sourceVisit?->examLines?->filter(fn ($l) => $l->service?->name);
                    @endphp
                    <tr>
                        <td class="py-2 text-gray-800">{{ $item->assured_name ?: '—' }}</td>
                        <td class="py-2 text-gray-600 text-xs">
                            @if ($sourceVisit?->objet)
                                <div class="font-medium text-gray-800">{{ $sourceVisit->objet }}</div>
                            @endif
                            @if ($designations && $designations->isNotEmpty())
                                @foreach ($designations as $line)
                                    <div>
                                        {{ $line->service->name }}
                                        @if ($line->service->code)<span class="text-gray-400">({{ $line->service->code }})</span>@endif
                                        @if ((float) $line->quantity > 1)<span class="text-gray-400">× {{ number_format((float) $line->quantity, 0, ',', ' ') }}</span>@endif
                                    </div>
                                @endforeach
                            @else
                                <div>{{ $item->description }}</div>
                            @endif
                            @if ($sourceVisit?->id && $sourceVisit->visit_date)
                                <div class="text-gray-400">{{ $sourceVisit->visit_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="py-2 text-right text-gray-600">{{ number_format($item->net_amount, 0, ',', ' ') }}</td>
                        <td class="py-2 text-center text-gray-600">{{ $item->coverage_rate !== null ? number_format((float) $item->coverage_rate, 0, ',', ' ').'%' : '—' }}</td>
                        <td class="py-2 text-right font-medium text-blue-600">{{ number_format($item->insurance_part, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-300 font-semibold">
                    <td colspan="2" class="py-2 text-gray-700">Sous-total {{ $company }}</td>
                    <td class="py-2 text-right text-gray-900">{{ number_format($items->sum('net_amount'), 0, ',', ' ') }}</td>
                    <td></td>
                    <td class="py-2 text-right text-blue-700">{{ number_format($items->sum('insurance_part'), 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endforeach
