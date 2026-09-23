<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Importer des données (Excel)</h2>
            <a href="{{ route('finance.exports') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Exports comptables
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded flex flex-wrap gap-2 items-center text-sm">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <span>
                    1. Téléchargez le modèle (.xlsx) correspondant · 2. Remplissez-le (une ligne d'exemple vous guide) · 3. Téléversez-le ici.
                    Les lignes en erreur sont signalées ligne par ligne et ne bloquent pas les autres.
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ($labels as $type => $label)
                    <div class="bg-white shadow-card rounded-2xl p-6 flex flex-col">
                        <h3 class="text-sm font-semibold text-gray-800">{{ $label }}</h3>
                        <p class="mt-1 text-xs text-gray-400 flex-1">{{ $descriptions[$type] }}</p>
                        <div class="mt-5 space-y-4">
                            <a href="{{ route('finance.import.template', $type) }}" class="inline-flex items-center gap-2 text-sm font-medium text-primary-600 hover:text-primary-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                Télécharger le modèle
                            </a>

                            @php
                                $route = route('finance.import.store', $type);
                            @endphp
                            <form method="POST" action="{{ $route }}" enctype="multipart/form-data" x-data="{ dragging: false }"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="dragging = false; $refs.file.files = $event.dataTransfer.files">
                                @csrf
                                <label class="block">
                                    <span class="sr-only">Fichier Excel</span>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                        x-ref="file"
                                        onchange="const name = this.files[0]?.name || ''; this.parentElement.querySelector('.file-label').textContent = name;"
                                        class="hidden">
                                    <span x-show="!dragging" class="cursor-pointer block w-full rounded-lg border-2 border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 hover:border-primary-400 hover:text-primary-600 transition file-label">Cliquez pour choisir un fichier .xlsx / .csv</span>
                                </label>
                                <button type="submit" class="mt-3 w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                    Importer
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>