<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Devis {{ $devis->number }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('devis.print', $devis) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Imprimer
                </a>
                @if ($devis->status === 'brouillon')
                    <a href="{{ route('devis.edit', $devis) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Modifier
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            @if ($devis->status === 'converti' && $devis->invoice)
                <div class="bg-purple-100 border border-purple-400 text-purple-800 px-4 py-3 rounded">
                    Ce devis a été converti en facture :
                    <a href="{{ route('invoices.show', $devis->invoice) }}" class="underline font-medium">{{ $devis->invoice->number }}</a>.
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Sidebar infos --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Statut</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $devis->status_badge }}">
                                        {{ ucfirst($devis->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Date</dt>
                                <dd class="text-gray-900">{{ $devis->date->format('d/m/Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Valide jusqu'au</dt>
                                <dd class="text-gray-900">{{ $devis->due_date->format('d/m/Y') }}</dd>
                            </div>
                            @if ($devis->currency !== 'XAF')
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Devise</dt>
                                <dd class="text-gray-900">{{ $devis->currency }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Créé par</dt>
                                <dd class="text-gray-900">{{ $devis->creator->name ?? '—' }}</dd>
                            </div>
                            @if ($devis->notes)
                            <div class="border-t border-gray-100 pt-3">
                                <dt class="text-gray-500">Notes</dt>
                                <dd class="text-gray-900 mt-1">{{ $devis->notes }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Client</h3>
                        <dl class="space-y-2 text-sm">
                            @if ($devis->client)
                                <div>
                                    <a href="{{ route('clients.show', $devis->client) }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $devis->client->name }}
                                    </a>
                                </div>
                                <div class="text-gray-600">{{ $devis->client->phone }}</div>
                                @if ($devis->client->email)
                                    <div class="text-gray-600">{{ $devis->client->email }}</div>
                                @endif
                            @else
                                <div class="font-medium text-gray-900">{{ $devis->walk_in_name }}</div>
                                <div class="text-xs text-gray-400">Client de passage (sans fiche)</div>
                            @endif
                            @if ($devis->agent)
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="text-xs text-gray-400">Agent</div>
                                    <div class="text-gray-900">{{ $devis->agent->name }}</div>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Actions statut --}}
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Actions</h3>
                        <div class="space-y-2">
                            @if ($devis->status !== 'converti')
                            <form method="POST" action="{{ route('devis.status', $devis) }}">
                                @csrf @method('PATCH')
                                <div class="flex gap-2">
                                    <select name="status" class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
                                        <option value="brouillon" {{ $devis->status === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                        <option value="envoye" {{ $devis->status === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                                        <option value="accepte" {{ $devis->status === 'accepte' ? 'selected' : '' }}>Accepté</option>
                                        <option value="refuse" {{ $devis->status === 'refuse' ? 'selected' : '' }}>Refusé</option>
                                    </select>
                                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-3 py-1.5 rounded-lg">OK</button>
                                </div>
                            </form>

                            @if (in_array($devis->status, ['envoye', 'accepte']))
                            <form method="POST" action="{{ route('devis.convert', $devis) }}">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
                                    onclick="return confirm('Convertir ce devis en facture ? Un devis n\'est pas modifiable après conversion.')">
                                    Convertir en facture
                                </button>
                            </form>
                            @endif
                            @endif

                            @if ($devis->status === 'brouillon')
                            <form method="POST" action="{{ route('devis.destroy', $devis) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
                                    onclick="return confirm('Supprimer ce devis ?')">
                                    Supprimer
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Contenu principal --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-card rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Lignes de devis</h3>

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
                                    @foreach ($devis->items as $item)
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

                        <div class="mt-4 flex justify-end">
                            <div class="w-72 space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Sous-total HT :</span>
                                    <span class="font-medium">{{ number_format($devis->subtotal, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @if ($devis->discount_amount > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>Remise :</span>
                                    <span class="font-medium">- {{ number_format($devis->discount_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-500">TVA ({{ $devis->tax_rate }}%) :</span>
                                    <span class="font-medium">{{ number_format($devis->tax_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-300 pt-1">
                                    <span class="font-semibold">Total TTC :</span>
                                    <span class="font-bold text-lg">{{ number_format($devis->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>