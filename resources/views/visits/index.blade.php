<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Visites médicales</h2>
            <div class="flex gap-2">
                <a href="{{ route('visits.calendar') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Calendrier
                </a>
                <a href="{{ route('visits.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    + Nouvelle visite
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex flex-wrap gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher entreprise, agent, objet..."
                            @input.debounce.400ms="$el.form.submit()"
                            class="flex-1 min-w-[220px] rounded-lg border-gray-300 shadow-sm text-sm">
                        <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">Tous les statuts</option>
                            <option value="planifiee" {{ request('status') === 'planifiee' ? 'selected' : '' }}>Planifiée</option>
                            <option value="realisee" {{ request('status') === 'realisee' ? 'selected' : '' }}>Réalisée</option>
                            <option value="annulee" {{ request('status') === 'annulee' ? 'selected' : '' }}>Annulée</option>
                            <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filtrer</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entreprise</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Examens</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($visits as $visit)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $visit->visit_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $visit->client->name }}</td>
                                    <td class="px-6 py-4 whitespace-normal text-sm text-gray-600">{{ $visit->assured_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'planifiee' => 'bg-blue-100 text-blue-800',
                                                'realisee' => 'bg-green-100 text-green-800',
                                                'annulee' => 'bg-red-100 text-red-800',
                                                'absent' => 'bg-yellow-100 text-yellow-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$visit->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($visit->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $visit->exam_lines_count ?? $visit->examLines->count() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <a href="{{ route('visits.show', $visit) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                        <a href="{{ route('visits.edit', $visit) }}" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        <form method="POST" action="{{ route('visits.destroy', $visit) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">Aucune visite trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">{{ $visits->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
