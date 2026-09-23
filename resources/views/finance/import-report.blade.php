<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Rapport d'import — {{ $importer->label() }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php $s = $result->summary(); @endphp

            @if ($s['errors'] === 0 && $s['imported'] > 0)
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded font-medium">
                    Import réussi : {{ $s['imported'] }} ligne(s) importée(s).
                </div>
            @elseif ($s['imported'] === 0)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded font-medium">
                    Aucune ligne importée. {{ $s['errors'] }} erreur(s) détectée(s).
                </div>
            @else
                <div class="bg-amber-100 border border-amber-400 text-amber-800 px-4 py-3 rounded font-medium">
                    Import partiel : {{ $s['imported'] }} ligne(s) importée(s), {{ $s['errors'] }} erreur(s).
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-white shadow-card rounded-2xl p-4 text-center">
                    <div class="text-2xl font-bold text-emerald-600">{{ $s['imported'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Importées</div>
                </div>
                <div class="bg-white shadow-card rounded-2xl p-4 text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $s['created'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Créées</div>
                </div>
                <div class="bg-white shadow-card rounded-2xl p-4 text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $s['updated'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Mises à jour</div>
                </div>
                <div class="bg-white shadow-card rounded-2xl p-4 text-center">
                    <div class="text-2xl font-bold text-gray-400">{{ $s['skipped'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Ignorées</div>
                </div>
                <div class="bg-white shadow-card rounded-2xl p-4 text-center">
                    <div class="text-2xl font-bold {{ $s['errors'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $s['errors'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Erreurs</div>
                </div>
            </div>

            @if ($s['errors'] > 0)
                <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-red-700">Détail des erreurs</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ligne</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Erreur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($result->errors as $error)
                                    <tr>
                                        <td class="px-6 py-3 text-gray-500 whitespace-nowrap">{{ $error['line'] }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($s['warnings'] > 0)
                <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-amber-600">Avertissements ({{ $s['warnings'] }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($result->warnings as $warning)
                                    <tr>
                                        <td class="px-6 py-3 text-gray-500 whitespace-nowrap">Ligne {{ $warning['line'] }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $warning['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ route('finance.imports') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700">← Retour aux imports</a>
                <a href="{{ route('finance.exports') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Aller aux exports comptables</a>
            </div>
        </div>
    </div>
</x-app-layout>