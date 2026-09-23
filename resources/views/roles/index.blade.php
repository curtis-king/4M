<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Rôles &amp; permissions</h2>
            <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau rôle
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4 text-sm text-indigo-800 flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
                <p class="text-gray-700">
                    Créez et personnalisez les profils : chaque rôle donne accès à des <strong>rubriques</strong> (clients, factures, devis…) et à des <strong>actions</strong> (voir, créer, modifier, supprimer…).
                    Les montants et le chiffre d'affaires ne sont visibles qu'aux rôles disposant de la permission <strong>« Voir le chiffre d'affaires et les montants »</strong>.
                </p>
            </div>

            <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accès (rubriques)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateurs</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $groupsMap = config('permissions', []);
                            @endphp
                            @forelse ($roles as $role)
                                @php
                                    $roleName = $role->label ?? ucfirst(str_replace('_', ' ', $role->name));
                                    $isAdmin = $role->name === 'administrateur';
                                    $visibleGroups = $role->permissions->pluck('name')
                                        ->map(fn ($p) => $groupsMap[$p]['group'] ?? null)
                                        ->filter()->unique()->sort()->values();
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold
                                                {{ $isAdmin ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ collect(explode(' ', $roleName))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">{{ $roleName }}</span>
                                                @if ($isAdmin)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Accès total</span>
                                                @endif
                                                <div class="text-xs text-gray-400">{{ $role->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($visibleGroups as $group)
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $group }}</span>
                                            @empty
                                                <span class="text-xs text-gray-400">Aucun accès</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">{{ $role->permissions->count() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">{{ $role->users_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('roles.edit', $role) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Modifier">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                            </a>
                                            @if (! $isAdmin)
                                                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600" title="Supprimer"
                                                        onclick="return confirm('Supprimer le rôle « {{ $roleName }} » ?')">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-sm text-gray-500">
                                        Aucun rôle. <a href="{{ route('roles.create') }}" class="text-primary-600 hover:text-primary-800 font-medium">+ Créer un rôle</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>