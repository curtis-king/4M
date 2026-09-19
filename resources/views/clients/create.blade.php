<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Nouveau client</h2>
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

            <form method="POST" action="{{ route('clients.store') }}" x-data="clientForm()" class="space-y-5">
                @csrf
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

                    {{-- Name --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span x-text="['entreprise', 'assureur'].includes(clientType) ? 'Raison sociale' : 'Nom complet'"></span> *
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                :placeholder="['entreprise', 'assureur'].includes(clientType) ? 'SARL ...' : 'Kabongo Pierre'"
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" inputmode="tel" pattern="[0-9+]{8,20}" required placeholder="+242 06 XXX XXXX"
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <select name="city" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">—</option>
                                @foreach (['Brazzaville', 'Pointe-Noire', 'Dolisie', 'Nkayi', 'Ouésso', 'Impfondo'] as $c)
                                <option value="{{ $c }}" {{ old('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Quartier, avenue..."
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Entreprise fields --}}
                    <div x-show="clientType === 'entreprise'" x-cloak class="mt-5 pt-5 border-t border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom entreprise</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                                <input type="text" name="company_nif" value="{{ old('company_nif') }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RC</label>
                                <input type="text" name="company_rcs" value="{{ old('company_rcs') }}"
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type destinataire *</label>
                                <select name="recipient_type" x-model="recipientType" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="individual">Particulier</option>
                                    <option value="business">Entreprise</option>
                                    <option value="government">Gouvernement</option>
                                    <option value="foreign">Étranger</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIU <span x-show="recipientType !== 'individual'" class="text-red-400">*</span></label>
                                <input type="text" name="niu" value="{{ old('niu') }}" :required="recipientType !== 'individual'"
                                    maxlength="17" pattern="[MP][A-Za-z0-9]{15,16}" inputmode="text"
                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RCCM <span x-show="recipientType === 'business'" class="text-red-400">*</span></label>
                                <input type="text" name="rccm" value="{{ old('rccm') }}" :required="recipientType === 'business'"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_taxable" value="1" {{ old('is_taxable', '1') ? 'checked' : '' }}
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
                                <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="tel" name="contact_phone" inputmode="tel" pattern="[0-9+]{8,20}" value="{{ old('contact_phone') }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Relier à un assureur / entreprise (facultatif) --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-700">Relier à un assureur ou une entreprise</h4>
                            <span class="text-xs bg-gray-50 text-gray-500 px-2 py-0.5 rounded font-medium">Facultatif</span>
                        </div>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden mb-4">
                            <button type="button" @click="linkType = ''"
                                :class="linkType === '' ? 'bg-gray-800 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                class="flex-1 py-2 text-xs font-medium transition">Aucun</button>
                            <button type="button" @click="linkType = 'assureur'"
                                :class="linkType === 'assureur' ? 'bg-purple-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                class="flex-1 py-2 text-xs font-medium transition border-x border-gray-200">À un assureur</button>
                            <button type="button" @click="linkType = 'entreprise'"
                                :class="linkType === 'entreprise' ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                class="flex-1 py-2 text-xs font-medium transition">À une entreprise</button>
                        </div>

                        <input type="hidden" name="link_type" x-model="linkType">

                        <div x-show="linkType === 'assureur'" x-cloak>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Assureur *</label>
                                    <select name="assureur_id" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">—</option>
                                        @foreach ($assureurs as $assureur)
                                        <option value="{{ $assureur->id }}" @selected(old('assureur_id')==$assureur->id)>{{ $assureur->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Taux de couverture (%) *</label>
                                    <input type="text" inputmode="decimal" name="coverage_rate" value="{{ old('coverage_rate') }}"
                                        placeholder="Ex : 80"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div x-show="linkType === 'entreprise'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Entreprise *</label>
                            <select name="entreprise_id" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">—</option>
                                @foreach ($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}" @selected(old('entreprise_id')==$entreprise->id)>{{ $entreprise->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Le client sera rattaché comme membre/agent de cette entreprise.</p>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('clients.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                        Créer le client
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function clientForm() {
            return {
                clientType: '{{ old('type', $selectedType ?? 'particulier') }}',
                recipientType: '{{ old('recipient_type', 'individual') }}',
                linkType: '{{ old('link_type', '') }}',
            }
        }
    </script>
</x-app-layout>