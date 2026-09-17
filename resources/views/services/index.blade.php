<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Catalogue des examens</h2>
            <a href="{{ route('services.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Nouveau service
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="border-b border-gray-200 px-6 pt-4 flex flex-wrap gap-2">
                    <a href="{{ route('services.index', request()->except(['group', 'category_id'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $group === null ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Toutes
                    </a>
                    <a href="{{ route('services.index', array_merge(request()->except(['group', 'category_id']), ['group' => 'medical'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $group === 'medical' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Analyses médicales
                    </a>
                    <a href="{{ route('services.index', array_merge(request()->except(['group', 'category_id']), ['group' => 'alimentaire'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $group === 'alimentaire' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Contrôle alimentaire
                    </a>
                </div>

                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex gap-3">
                        @if ($group)
                            <input type="hidden" name="group" value="{{ $group }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un examen..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <select name="category_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">Toutes les catégories</option>
                            @php $medicalCats = $allCategories->filter(fn ($c) => ! $c->is_alimentaire); @endphp
                            @php $alimCats = $allCategories->filter(fn ($c) => $c->is_alimentaire); @endphp
                            @if ($medicalCats->isNotEmpty())
                                <optgroup label="Analyses médicales">
                                    @foreach ($medicalCats as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($alimCats->isNotEmpty())
                                <optgroup label="Contrôle alimentaire">
                                    @foreach ($alimCats as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                    </form>
                </div>

                @forelse ($categories as $cat)
                    @php $catServices = $grouped->get($cat->id, collect()); @endphp
                    @if ($catServices->isEmpty())
                        @continue
                    @endif
                    <div class="{{ ! $loop->first ? 'border-t border-gray-200' : '' }}">
                        <div class="px-6 py-3 flex items-center justify-between {{ $cat->is_alimentaire ? 'bg-emerald-50 border-b border-emerald-100' : 'bg-blue-50 border-b border-blue-100' }}">
                            <div>
                                <h3 class="font-semibold text-sm {{ $cat->is_alimentaire ? 'text-emerald-800' : 'text-blue-800' }}">{{ $cat->name }}</h3>
                                @if ($cat->description)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $cat->description }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-500">{{ $catServices->count() }} {{ $catServices->count() > 1 ? 'examens' : 'examen' }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code SFEC</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Prix (FCFA)</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($catServices as $service)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 text-sm">{{ $service->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $service->code ?? '—' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $service->classification_code ?? '—' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ number_format($service->price, 0, ',', ' ') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($service->is_active)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Actif</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactif</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                                <a href="{{ route('services.edit', $service) }}" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                                <form method="POST" action="{{ route('services.destroy', $service) }}" class="inline" onsubmit="return confirm('Supprimer ce service ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-500">Aucun service trouvé.</div>
                @endforelse
            </div>

            @if ($group === 'alimentaire')
                <div class="mt-6 bg-white shadow-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Visites de contrôle alimentaire récentes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Résultat</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($visits as $visit)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">{{ $visit->visit_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $visit->client->name }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">{{ $visit->produit_alimentaire }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">{{ $visit->numero_lot ?? '—' }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm capitalize text-gray-600">{{ $visit->type_controle ?? '—' }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-center">
                                            @if ($visit->statut_resultat === 'conforme')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Conforme</span>
                                            @elseif ($visit->statut_resultat === 'non_conforme')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Non conforme</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">En attente</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ number_format($visit->total, 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">Aucune visite de contrôle alimentaire enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>