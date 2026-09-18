<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Devis</h2>
            <a href="{{ route('devis.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Nouveau devis
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex flex-wrap gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° devis, client..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 min-w-[200px] rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                            <option value="envoye" {{ request('status') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                            <option value="accepte" {{ request('status') === 'accepte' ? 'selected' : '' }}>Accepté</option>
                            <option value="refuse" {{ request('status') === 'refuse' ? 'selected' : '' }}>Refusé</option>
                            <option value="converti" {{ request('status') === 'converti' ? 'selected' : '' }}>Converti</option>
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date début">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm" title="Date fin">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                        @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                            <a href="{{ route('devis.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Réinitialiser</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Devis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($devis as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('devis.show', $item) }}" class="text-blue-600 hover:underline font-medium text-sm">
                                            {{ $item->number }}
                                        </a>
                                        @if ($item->invoice)
                                            <span class="ml-1 text-xs text-purple-600" title="Converti en {{ $item->invoice->number }}">→ Facture</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $item->recipient_name }}
                                        @unless ($item->client)
                                            <span class="text-xs text-gray-400">(passage)</span>
                                        @endunless
                                        @if ($item->agent)
                                            <div class="text-xs text-gray-400">{{ $item->agent->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $item->due_date->format('d/m/Y') }}
                                        @if ($item->due_date->lt(now()) && !in_array($item->status, ['converti', 'refuse']))
                                            <span class="ml-1 text-xs text-red-500">(expiré)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->status_badge }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        {{ number_format($item->total, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <a href="{{ route('devis.show', $item) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                        @if ($item->status === 'brouillon')
                                            <a href="{{ route('devis.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Aucun devis trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">
                    {{ $devis->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>