<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Nouveau réactif</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <form method="POST" action="{{ route('reagents.store') }}" class="p-6 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom du réactif *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700">Référence fournisseur</label>
                            <input type="text" name="reference" id="reference" value="{{ old('reference') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700">Unité *</label>
                            <input type="text" name="unit" id="unit" value="{{ old('unit') }}" required placeholder="ml, g, kits..."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('unit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Stock actuel *</label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" min="0" step="0.01" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="min_quantity" class="block text-sm font-medium text-gray-700">Seuil minimum (alerte) *</label>
                            <input type="number" name="min_quantity" id="min_quantity" value="{{ old('min_quantity', 0) }}" min="0" step="0.01" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="max_quantity" class="block text-sm font-medium text-gray-700">Stock maximum</label>
                            <input type="number" name="max_quantity" id="max_quantity" value="{{ old('max_quantity') }}" min="0" step="0.01"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('reagents.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
