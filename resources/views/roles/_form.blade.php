@php
    $assigned = $assigned ?? [];
@endphp
<div class="space-y-6">
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-card rounded-2xl p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nom technique *</label>
                <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}"
                    placeholder="ex : secretaire, biologiste..."
                    @if (isset($role) && $role->name === 'administrateur') disabled @endif
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <p class="mt-1 text-xs text-gray-400">Identifiant unique : lettres minuscules, chiffres et underscores.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Appellation affichée</label>
                <input type="text" name="label" value="{{ old('label', $role->label ?? '') }}"
                    placeholder="ex : Secrétaire"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <p class="mt-1 text-xs text-gray-400">Libellé visible dans l'application (utilisateurs, tableau de bord…).</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-card rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-gray-800">Permissions</h3>
        <p class="text-xs text-gray-400 mb-4">Cochez les rubriques et actions autorisées pour ce rôle.</p>

        <div class="space-y-6">
            @foreach ($catalogue as $group => $permissions)
                <div x-data="{ all: false }" class="rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h4 class="text-sm font-semibold text-gray-700">{{ $group }}</h4>
                        <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer select-none">
                            <input type="checkbox"
                                x-model="all"
                                @change="document.querySelectorAll('#group-{{ \Illuminate\Support\Str::slug($group) }} input[type=checkbox]').forEach(c => c.checked = all)">
                            Tout cocher
                        </label>
                    </div>
                    <div id="group-{{ \Illuminate\Support\Str::slug($group) }}" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission['name'] }}"
                                    {{ in_array($permission['name'], $assigned, true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $permission['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>