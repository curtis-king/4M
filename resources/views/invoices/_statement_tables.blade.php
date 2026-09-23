@php
    $canFin = auth()->user()->can('view financial data');
    $statementGroups = $invoice->items->groupBy(fn ($item) => $item->company_name ?: 'Divers');
@endphp

<div class="space-y-5">
    @foreach ($statementGroups as $company => $items)
        <div>
            <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ $company }}</h4>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Assuré</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Objet / prestation</th>
                            @if ($canFin)
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant complet</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Couv.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Part assureur</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-900 whitespace-nowrap">
                                    @if ($item->assured_name)
                                        <a href="{{ $item->sourceVisit?->id ? route('visits.show', $item->sourceVisit) : '#' }}"
                                            class="font-medium text-gray-900 hover:text-blue-600">{{ $item->assured_name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @php
                                        $sourceVisit = $item->sourceVisit;
                                        $designations = $sourceVisit?->examLines?->filter(fn ($l) => $l->service?->name);
                                    @endphp
                                    @if ($sourceVisit?->objet)
                                        <div class="font-medium text-gray-900">{{ $sourceVisit->objet }}</div>
                                    @endif
                                    @if ($designations && $designations->isNotEmpty())
                                        <ul class="space-y-0.5">
                                            @foreach ($designations as $line)
                                                <li class="text-gray-700">
                                                    {{ $line->service->name }}
                                                    @if ($line->service->code)<span class="text-xs text-gray-400">({{ $line->service->code }})</span>@endif
                                                    @if ((float) $line->quantity > 1)<span class="text-xs text-gray-400">× {{ number_format((float) $line->quantity, 0, ',', ' ') }}</span>@endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div>{{ $item->description }}</div>
                                    @endif
                                    @if ($sourceVisit?->id && $sourceVisit->visit_date)
                                        <div class="text-xs text-gray-400">{{ $sourceVisit->visit_date->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                @if ($canFin)
                                <td class="px-4 py-2 text-right text-gray-600">{{ number_format($item->net_amount, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $item->coverage_rate !== null ? number_format((float) $item->coverage_rate, 0, ',', ' ').'%' : '—' }}</td>
                                <td class="px-4 py-2 text-right font-medium text-blue-700">{{ number_format($item->insurance_part, 0, ',', ' ') }}</td>
                            @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sous-total {{ $company }}</td>
                            @if ($canFin)
                                <td class="px-4 py-2 text-right font-semibold text-gray-900">{{ number_format($items->sum('net_amount'), 0, ',', ' ') }}</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right font-semibold text-blue-700">{{ number_format($items->sum('insurance_part'), 0, ',', ' ') }}</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
</div>