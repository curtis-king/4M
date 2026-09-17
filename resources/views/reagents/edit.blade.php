<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Modifier — {{ $reagent->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <form method="POST" action="{{ route('reagents.update', $reagent) }}" class="p-6 space-y-6">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
                            <input type="text" name="name" value="{{ old('name', $reagent->name) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700">Référence</label>
                            <input type="text" name="reference" value="{{ old('reference', $reagent->reference) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700">Unité *</label>
                            <input type="text" name="unit" value="{{ old('unit', $reagent->unit) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Stock actuel *</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $reagent->quantity) }}" min="0" step="0.01" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="min_quantity" class="block text-sm font-medium text-gray-700">Seuil min. *</label>
                            <input type="number" name="min_quantity" value="{{ old('min_quantity', $reagent->min_quantity) }}" min="0" step="0.01" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="max_quantity" class="block text-sm font-medium text-gray-700">Stock max.</label>
                            <input type="number" name="max_quantity" value="{{ old('max_quantity', $reagent->max_quantity) }}" min="0" step="0.01"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('reagents.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
