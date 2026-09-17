<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Assureurs</h2>
                <p class="text-sm text-gray-500 mt-0.5">Compagnies d'assurance</p>
            </div>
            <a href="{{ route('clients.create', ['type' => 'assureur']) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter un assureur
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-card rounded-2xl overflow-hidden" x-data="insurerList()">
                <div class="p-6 border-b border-gray-200">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-model="q" placeholder="Rechercher une compagnie, un contact, un téléphone..."
                            class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compagnie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Assurés</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Remise</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="i in filtered" :key="i.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-sm font-bold text-purple-700" x-text="i.initials"></div>
                                            <div>
                                                <div class="text-gray-900 font-medium text-sm" x-text="i.name"></div>
                                                <div class="text-xs text-gray-400" x-show="i.contact_name" x-text="i.contact_name"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div x-text="i.phone"></div>
                                        <div class="text-xs text-gray-400" x-show="i.email" x-text="i.email"></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-600">
                                            <span x-text="i.contracts_count"></span> assuré(s)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700" x-show="i.discount_rate">
                                            <span x-text="fmt(i.discount_rate)"></span>%
                                        </span>
                                        <span class="text-xs text-gray-400" x-show="!i.discount_rate">—</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a :href="i.url" class="text-blue-600 hover:text-blue-800 font-medium">Gérer les assurés</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!filtered.length">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm text-gray-500">Aucun assureur.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function insurerList() {
            return {
                q: '',
                insurers: @json($insurerRows),
                get filtered() {
                    const s = (this.q || '').toLowerCase().trim();
                    if (!s) return this.insurers;
                    return this.insurers.filter(i =>
                        (i.name + ' ' + (i.contact_name || '') + ' ' + (i.phone || '') + ' ' + (i.email || '')).toLowerCase().includes(s)
                    );
                },
                fmt(n) {
                    return String(parseFloat(n).toFixed(1)).replace('.', ',');
                },
            }
        }
    </script>
</x-app-layout>