<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Paramètres du laboratoire</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                @csrf @method('PUT')

                <div class="bg-white shadow-card rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations du laboratoire</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nom du laboratoire *</label>
                            <input type="text" name="name" value="{{ old('name', $settings->name) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Adresse *</label>
                            <textarea name="address" rows="2" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('address', $settings->address) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone *</label>
                            <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" value="{{ old('email', $settings->email) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIU *</label>
                            <input type="text" name="niu" value="{{ old('niu', $settings->niu) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIF *</label>
                            <input type="text" name="nif" value="{{ old('nif', $settings->nif) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RC *</label>
                            <input type="text" name="rc" value="{{ old('rc', $settings->rc) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Patente</label>
                            <input type="text" name="patente" value="{{ old('patente', $settings->patente) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CNSS</label>
                            <input type="text" name="cnss" value="{{ old('cnss', $settings->cnss) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-card rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations bancaires</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Banque</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $settings->bank_name) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RIB</label>
                            <input type="text" name="bank_rib" value="{{ old('bank_rib', $settings->bank_rib) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-card rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Configuration SFEC</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Environnement</label>
                            <select name="sfec_environment"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="sandbox" {{ $settings->sfec_environment === 'sandbox' ? 'selected' : '' }}>Sandbox (test)</option>
                                <option value="production" {{ $settings->sfec_environment === 'production' ? 'selected' : '' }}>Production</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Logo (URL)</label>
                            <input type="text" name="logo" value="{{ old('logo', $settings->logo) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Clé API SFEC (production)</label>
                            <input type="password" name="sfec_api_key" value="{{ $settings->sfec_api_key }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm" autocomplete="off">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Clé API SFEC (sandbox)</label>
                            <input type="password" name="sfec_api_key_sandbox" value="{{ $settings->sfec_api_key_sandbox }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-sm" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                        Enregistrer les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
