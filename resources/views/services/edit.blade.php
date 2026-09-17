<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Modifier — {{ $service->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <form method="POST" action="{{ route('services.update', $service) }}" class="p-6 space-y-6">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie *</label>
                            <select name="category_id" id="category_id" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <optgroup label="Analyses médicales">
                                @foreach ($categories->filter(fn ($c) => ! $c->is_alimentaire) as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                                </optgroup>
                                <optgroup label="Contrôle alimentaire">
                                    @foreach ($categories->filter(fn ($c) => $c->is_alimentaire) as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Code interne</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $service->code) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="classification_code" class="block text-sm font-medium text-gray-700">Code SFEC</label>
                            <input type="text" name="classification_code" id="classification_code" value="{{ old('classification_code', $service->classification_code) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Prix (FCFA) *</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $service->price) }}" min="0" step="100" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ms-2 text-sm text-gray-600">Actif</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="2"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('description', $service->description) }}</textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('services.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
