<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.show', $client) }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">{{ $client->name }}</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('clients.update', $client) }}" x-data="clientForm()" class="space-y-5">
                @csrf @method('PUT')
                <input type="hidden" name="type" x-model="clientType">

                <div class="bg-white shadow-card rounded-2xl p-6">
                    {{-- Type selector --}}
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden mb-6">
                        <button type="button" @click="clientType = 'assureur'"
                            :class="clientType === 'assureur' ? 'bg-purple-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                            class="flex-1 py-2.5 text-sm font-medium transition">
                            Assureur
                        </button>
                        <button type="button" @click="clientType = 'entreprise'"
                            :class="clientType === 'entreprise' ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                            class="flex-1 py-2.5 text-sm font-medium transition border-x border-gray-200">
                            Entreprise
                        </button>
                        <button type="button" @click="clientType = 'particulier'"
                            :class="clientType === 'particulier' ? 'bg-gray-800 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                            class="flex-1 py-2.5 text-sm font-medium transition">
                            Particulier
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span x-text="['entreprise', 'assureur'].includes(clientType) ? 'Raison sociale' : 'Nom complet'"></span> *
                            </label>
                            <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <select name="city" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">—</option>
                                @foreach (['Brazzaville', 'Pointe-Noire', 'Dolisie', 'Nkayi', 'Ouésso', 'Impfondo'] as $c)
                                    <option value="{{ $c }}" {{ old('city', $client->city) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input type="text" name="address" value="{{ old('address', $client->address) }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Entreprise --}}
                    <div x-show="clientType === 'entreprise'" x-cloak class="mt-5 pt-5 border-t border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom entreprise</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                                <input type="text" name="company_nif" value="{{ old('company_nif', $client->company_nif) }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RC</label>
                                <input type="text" name="company_rcs" value="{{ old('company_rcs', $client->company_rcs) }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- ID Fiscale --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-700">Identification fiscale</h4>
                            <span x-show="recipientType !== 'individual'" x-cloak class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded font-medium">Obligatoire</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIU <span x-show="recipientType !== 'individual'" class="text-red-400">*</span></label>
                                <input type="text" name="niu" value="{{ old('niu', $client->niu) }}" :required="recipientType !== 'individual'"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RCCM <span x-show="recipientType === 'business'" class="text-red-400">*</span></label>
                                <input type="text" name="rccm" value="{{ old('rccm', $client->rccm) }}" :required="recipientType === 'business'"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type destinataire *</label>
                                <select name="recipient_type" x-model="recipientType" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="individual">Particulier</option>
                                    <option value="business">Entreprise</option>
                                    <option value="government">Gouvernement</option>
                                    <option value="foreign">Étranger</option>
                                </select>
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_taxable" value="1" {{ old('is_taxable', $client->is_taxable) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                    <span class="text-sm text-gray-700">Assujetti TVA</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div x-show="clientType !== 'particulier'" x-cloak class="mt-5 pt-5 border-t border-gray-100">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Personne de contact</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                <input type="text" name="contact_name" value="{{ old('contact_name', $client->contact_name) }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $client->contact_phone) }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $client->notes) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function clientForm() {
            return {
                clientType: '{{ old('type', $client->type) }}',
                recipientType: '{{ old('recipient_type', $client->recipient_type ?? 'individual') }}',
            }
        }
    </script>
</x-app-layout>
