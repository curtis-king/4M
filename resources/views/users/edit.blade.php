<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Modifier l'utilisateur</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                @csrf @method('PUT')

                <div class="bg-white shadow-card rounded-2xl p-6 space-y-4">
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                            <input type="password" name="password"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="Laisser vide pour ne pas changer">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirmation</label>
                            <input type="password" name="password_confirmation"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rôle *</label>
                        @php $selected = $user->roles->first()->name ?? null; @endphp
                        @include('users.partials.role-select')
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
