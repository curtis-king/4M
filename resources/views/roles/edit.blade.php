<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            Modifier le rôle « {{ $role->label ?? ucfirst(str_replace('_', ' ', $role->name)) }} »
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
                @csrf @method('PUT')
                @include('roles._form')
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('roles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>