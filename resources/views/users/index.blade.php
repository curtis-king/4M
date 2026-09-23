<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Utilisateurs</h2>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvel utilisateur
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            @php
                $roleLabels = [
                    'administrateur' => 'Administrateur',
                    'comptable' => 'Comptable',
                    'secretaire' => 'Secrétaire',
                ];
                $roleColors = [
                    'administrateur' => 'text-red-600 bg-red-50',
                    'comptable' => 'text-emerald-600 bg-emerald-50',
                    'secretaire' => 'text-amber-600 bg-amber-50',
                ];
            @endphp

            <div class="bg-white shadow-card rounded-2xl">
                <div class="px-6 py-4 border-b border-gray-200">
                    <form method="GET" class="flex gap-2 items-center">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un utilisateur..."
                            class="border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 w-64">
                        <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">OK</button>
                        @if (request('search'))
                            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 text-sm ml-1">&times;</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-3">
                                        @foreach ($user->roles as $role)
                                            <span class="text-xs font-medium px-2 py-0.5 rounded {{ $roleColors[$role->name] ?? 'text-gray-600 bg-gray-50' }}">
                                                {{ $role->label ?? ($roleLabels[$role->name] ?? $role->name) }}
                                            </span>
                                        @endforeach
                                        @if ($user->roles->isEmpty())
                                            <span class="text-xs text-gray-300">Aucun rôle</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm">
                                        <a href="{{ route('users.edit', $user) }}" class="text-gray-400 hover:text-gray-600 mr-3">Modifier</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-500">Supprimer</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <p class="text-sm text-gray-500">Aucun utilisateur trouvé.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 border-t border-gray-100">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
