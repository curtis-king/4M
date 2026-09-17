<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Stock des réactifs</h2>
            <a href="{{ route('reagents.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Nouveau réactif
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            @if ($lowStockCount > 0)
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    ⚠ {{ $lowStockCount }} réactif(s) en stock bas !
                    <a href="{{ route('reagents.index', ['low_stock' => 1]) }}" class="underline ml-2">Voir</a>
                </div>
            @endif

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
                        <label class="flex items-center text-sm text-gray-600">
                            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                                onchange="this.form.submit()"
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                            Stock bas uniquement
                        </label>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unité</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Seuil min.</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($reagents as $reagent)
                                @php $isLow = $reagent->quantity <= $reagent->min_quantity; @endphp
                                <tr class="{{ $isLow ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 text-sm">{{ $reagent->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $reagent->reference ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $reagent->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $isLow ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $reagent->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">{{ $reagent->min_quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($isLow)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Stock bas</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OK</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <a href="{{ route('reagents.edit', $reagent) }}" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        <form method="POST" action="{{ route('reagents.destroy', $reagent) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Aucun réactif trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">{{ $reagents->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
