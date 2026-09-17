<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Clients</h2>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau client
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @php
                $total = \App\Models\Client::count();
                $nbAssureurs = \App\Models\Client::where('type', 'assureur')->count();
                $nbEntreprises = \App\Models\Client::where('type', 'entreprise')->count();
                $nbParticuliers = \App\Models\Client::where('type', 'particulier')->count();
            @endphp

            <div class="bg-white shadow-card rounded-2xl" x-data="{ tab: '{{ request('type', 'all') }}' }">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex gap-4 text-sm">
                            <button @click="tab = 'all'; $refs.filterType.value = ''; $refs.filterForm.submit()"
                                :class="tab === 'all' ? 'text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                class="transition">
                                Tous <span class="ml-1 text-xs text-gray-400">({{ $total }})</span>
                            </button>
                            <button @click="tab = 'assureur'; $refs.filterType.value = 'assureur'; $refs.filterForm.submit()"
                                :class="tab === 'assureur' ? 'text-purple-600 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                class="transition">
                                Assureurs <span class="ml-1 text-xs text-gray-400">({{ $nbAssureurs }})</span>
                            </button>
                            <button @click="tab = 'entreprise'; $refs.filterType.value = 'entreprise'; $refs.filterForm.submit()"
                                :class="tab === 'entreprise' ? 'text-blue-600 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                class="transition">
                                Entreprises <span class="ml-1 text-xs text-gray-400">({{ $nbEntreprises }})</span>
                            </button>
                            <button @click="tab = 'particulier'; $refs.filterType.value = 'particulier'; $refs.filterForm.submit()"
                                :class="tab === 'particulier' ? 'text-gray-600 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                class="transition">
                                Particuliers <span class="ml-1 text-xs text-gray-400">({{ $nbParticuliers }})</span>
                            </button>
                        </div>
                        <form method="GET" x-ref="filterForm" class="flex gap-2 items-center">
                            <input type="hidden" name="type" x-ref="filterType" value="{{ request('type') }}">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                                @input.debounce.400ms="$refs.filterForm.submit()"
                                class="border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 w-64">
                            <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">OK</button>
                            @if (request()->hasAny(['search', 'type']))
                                <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-gray-600 text-sm ml-1">&times;</a>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID. Fiscale</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Factures</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $badgeColors = [
                                    'assureur' => 'text-purple-600 bg-purple-50',
                                    'entreprise' => 'text-blue-600 bg-blue-50',
                                    'particulier' => 'text-gray-500 bg-gray-50',
                                ];
                            @endphp
                            @forelse ($clients as $client)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3">
                                        <a href="{{ route('clients.show', $client) }}" class="text-gray-900 font-medium text-sm hover:text-blue-600">{{ $client->name }}</a>
                                        @if (in_array($client->type, ['assureur', 'entreprise']))
                                            <div class="text-xs {{ $client->type === 'assureur' ? 'text-purple-500' : 'text-blue-500' }} mt-0.5">{{ $client->insurance_contracts_count }} couverture(s)</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded {{ $badgeColors[$client->type] }}">{{ $client->display_type }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        <div>{{ $client->phone }}</div>
                                        @if ($client->email)<div class="text-xs text-gray-400">{{ $client->email }}</div>@endif
                                    </td>
                                    <td class="px-6 py-3 text-xs text-gray-500">
                                        @if ($client->niu)<div>{{ $client->niu }}</div>@endif
                                        @if ($client->rccm)<div>{{ $client->rccm }}</div>@endif
                                        @if (!$client->niu && !$client->rccm)<span class="text-gray-300">—</span>@endif
                                    </td>
                                    <td class="px-6 py-3 text-center text-sm text-gray-500">{{ $client->invoices_count }}</td>
                                    <td class="px-6 py-3 text-right text-sm">
                                        <a href="{{ route('clients.edit', $client) }}" class="text-gray-400 hover:text-gray-600 mr-3">Modifier</a>
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <p class="text-sm text-gray-500">Aucun client trouvé.</p>
                                        <a href="{{ route('clients.create') }}" class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium">+ Créer un client</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 border-t border-gray-100">
                    {{ $clients->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
