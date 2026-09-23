@php $canFin = auth()->user()->can('view financial data'); @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 leading-tight">{{ $client->name }}</h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        @php
                            $typeColors = [
                                'assureur' => 'text-purple-600',
                                'entreprise' => 'text-blue-600',
                                'particulier' => 'text-gray-500',
                            ];
                        @endphp
                        <span class="text-xs font-medium {{ $typeColors[$client->type] }}">{{ $client->display_type }}</span>
                        <span class="text-gray-300">·</span>
                        <span class="text-xs text-gray-400">{{ $client->phone }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('clients.edit', $client) }}" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Modifier
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white shadow-card rounded-2xl p-5 space-y-5">
                        {{-- Contact --}}
                        <div>
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Contact</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Téléphone</dt>
                                    <dd class="text-gray-900 font-medium">{{ $client->phone }}</dd>
                                </div>
                                @if ($client->email)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Email</dt>
                                    <dd class="text-gray-900">{{ $client->email }}</dd>
                                </div>
                                @endif
                                @if ($client->city)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Ville</dt>
                                    <dd class="text-gray-900">{{ $client->city }}</dd>
                                </div>
                                @endif
                                @if ($client->address)
                                <div>
                                    <dt class="text-gray-400">Adresse</dt>
                                    <dd class="text-gray-900 mt-0.5">{{ $client->address }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <div class="border-t border-gray-100"></div>

                        {{-- Entreprise --}}
                        @if ($client->company_name)
                        <div>
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Entreprise</h3>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="text-gray-400">Raison sociale</dt>
                                    <dd class="text-gray-900 font-medium mt-0.5">{{ $client->company_name }}</dd>
                                </div>
                                @if ($client->company_nif)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">NIF</dt>
                                    <dd class="text-gray-900">{{ $client->company_nif }}</dd>
                                </div>
                                @endif
                                @if ($client->company_rcs)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">RC</dt>
                                    <dd class="text-gray-900">{{ $client->company_rcs }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        @endif

                        {{-- ID Fiscale --}}
                        <div>
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Identification fiscale</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Destinataire</dt>
                                    <dd class="text-gray-900">{{ match($client->recipient_type) { 'business' => 'Entreprise', 'government' => 'Gouvernement', 'foreign' => 'Étranger', default => 'Particulier' } }}</dd>
                                </div>
                                @if ($client->niu)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">NIU</dt>
                                    <dd class="text-gray-900 font-mono text-xs">{{ $client->niu }}</dd>
                                </div>
                                @endif
                                @if ($client->rccm)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">RCCM</dt>
                                    <dd class="text-gray-900 font-mono text-xs">{{ $client->rccm }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">TVA</dt>
                                    <dd class="{{ $client->is_taxable ? 'text-green-600' : 'text-gray-400' }}">{{ $client->is_taxable ? 'Oui' : 'Non' }}</dd>
                                </div>
                            </dl>
                        </div>

                        @if ($client->contact_name)
                        <div class="border-t border-gray-100"></div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Contact</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Nom</dt>
                                    <dd class="text-gray-900">{{ $client->contact_name }}</dd>
                                </div>
                                @if ($client->contact_phone)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Téléphone</dt>
                                    <dd class="text-gray-900">{{ $client->contact_phone }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        @endif

                        @if ($client->notes)
                        <div class="border-t border-gray-100"></div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Notes</h3>
                            <p class="text-sm text-gray-600">{{ $client->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Main --}}
                <div class="lg:col-span-2" x-data="{ tab: 'invoices' }">
                    {{-- Tabs --}}
                    <div class="flex gap-6 border-b border-gray-200 mb-6">
                        <button @click="tab = 'invoices'"
                            :class="tab === 'invoices' ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="pb-3 text-sm font-medium border-b-2 transition">
                            Factures <span class="ml-1 text-xs text-gray-400">{{ $client->invoices_count }}</span>
                        </button>
                        <button @click="tab = 'agents'"
                            :class="tab === 'agents' ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="pb-3 text-sm font-medium border-b-2 transition">
                            Agents <span class="ml-1 text-xs text-gray-400">{{ $client->agents->count() }}</span>
                        </button>
                        <button @click="tab = 'sites'"
                            :class="tab === 'sites' ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="pb-3 text-sm font-medium border-b-2 transition">
                            Sites <span class="ml-1 text-xs text-gray-400">{{ $client->sites->count() }}</span>
                        </button>
                        <button @click="tab = 'contracts'"
                            :class="tab === 'contracts' ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="pb-3 text-sm font-medium border-b-2 transition">
                            Contrats <span class="ml-1 text-xs text-gray-400">{{ $client->insuranceContracts->count() }}</span>
                        </button>
                    </div>

                    {{-- Factures --}}
                    <div x-show="tab === 'invoices'" x-cloak>
                        @if ($invoices->count())
                        <div class="bg-white shadow-card rounded-2xl overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">N°</th>
                                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        @if ($canFin)
                                            <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Reste</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $invoice->number }}</a>
                                        </td>
                                        <td class="px-5 py-3 text-gray-500">{{ $invoice->date->format('d/m/Y') }}</td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $invoice->status_badge }}">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                        @if ($canFin)
                                        <td class="px-5 py-3 text-right font-medium text-gray-900">{{ number_format($invoice->total, 0, ',', ' ') }}</td>
                                        <td class="px-5 py-3 text-right {{ $invoice->amountDue > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                            {{ number_format($invoice->amountDue, 0, ',', ' ') }}
                                        </td>
                                    @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $invoices->withQueryString()->links() }}</div>
                        @else
                        <div class="text-center py-10">
                            <p class="text-sm text-gray-400">Aucune facture.</p>
                            <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">+ Créer une facture</a>
                        </div>
                        @endif
                    </div>

                    {{-- Agents --}}
                    <div x-show="tab === 'agents'" x-cloak>
                        <div class="bg-white shadow-card rounded-2xl p-5">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm font-medium text-gray-700">Agents / Employés</span>
                                <div x-data="{ open: false }">
                                    <button @click="open = !open" class="text-sm text-gray-800 font-medium hover:text-gray-600">+ Ajouter</button>
                                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-10">
                                        <form method="POST" action="{{ route('clients.agents.store', $client) }}" class="space-y-3">
                                            @csrf
                                            <input type="text" name="name" placeholder="Nom *" required class="block w-full rounded-lg border-gray-300 text-sm">
                                            <input type="text" name="matricule" placeholder="Matricule" class="block w-full rounded-lg border-gray-300 text-sm">
                                            <input type="text" name="phone" placeholder="Téléphone" class="block w-full rounded-lg border-gray-300 text-sm">
                                            <div class="flex gap-2 pt-1">
                                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1.5 rounded-lg">Ajouter</button>
                                                <button type="button" @click="open = false" class="text-sm text-gray-400 hover:text-gray-600 px-3 py-1.5">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if ($client->agents->count())
                            <div class="relative mb-4">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" data-list-filter data-target="#agents-list"
                                    placeholder="Rechercher un employé (nom ou matricule)..."
                                    class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                            <div id="agents-list" class="divide-y divide-gray-100">
                                @foreach ($client->agents as $agent)
                                <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
                                    data-search="{{ mb_strtolower($agent->name . ' ' . ($agent->matricule ?? '') . ' ' . ($agent->phone ?? '')) }}">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $agent->name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $agent->matricule ?? '—' }}{{ $agent->phone ? ' · ' . $agent->phone : '' }}</div>
                                    </div>
                                    <form method="POST" action="{{ route('clients.agents.destroy', [$client, $agent]) }}" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Supprimer</button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            <p id="agents-no-match" class="hidden text-sm text-gray-400 text-center py-6">Aucun employé ne correspond à la recherche.</p>
                            @else
                            <p class="text-sm text-gray-400 text-center py-6">Aucun agent.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Sites --}}
                    <div x-show="tab === 'sites'" x-cloak>
                        <div class="bg-white shadow-card rounded-2xl p-5">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm font-medium text-gray-700">Sites / établissements</span>
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-sm text-gray-800 font-medium hover:text-gray-600">+ Ajouter</button>
                                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-10 text-left">
                                        <form method="POST" action="{{ route('clients.sites.store', $client) }}" class="space-y-3">
                                            @csrf
                                            <input type="text" name="name" placeholder="Nom du site *" required class="block w-full rounded-lg border-gray-300 text-sm">
                                            <input type="text" name="city" placeholder="Ville (facultatif)" class="block w-full rounded-lg border-gray-300 text-sm">
                                            <input type="number" min="0" name="sort_order" placeholder="Ordre (facultatif)" class="block w-full rounded-lg border-gray-300 text-sm">
                                            <div class="flex gap-2 pt-1">
                                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1.5 rounded-lg">Ajouter</button>
                                                <button type="button" @click="open = false" class="text-sm text-gray-400 hover:text-gray-600 px-3 py-1.5">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if ($client->sites->count())
                            <div class="divide-y divide-gray-100">
                                @foreach ($client->sites as $site)
                                <div x-data="{ editing: false }" class="py-3 first:pt-0 last:pb-0">
                                    <div x-show="!editing" class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $site->name }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">{{ $site->city ?: '—' }}</div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="editing = true" class="text-xs text-gray-400 hover:text-gray-700">Modifier</button>
                                            <form method="POST" action="{{ route('clients.sites.destroy', [$client, $site]) }}" onsubmit="return confirm('Supprimer ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                    <form x-show="editing" x-cloak method="POST" action="{{ route('clients.sites.update', [$client, $site]) }}" class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $site->name }}" required placeholder="Nom du site *" class="block w-full rounded-lg border-gray-300 text-sm">
                                        <input type="text" name="city" value="{{ $site->city }}" placeholder="Ville" class="block w-40 rounded-lg border-gray-300 text-sm">
                                        <button type="submit" class="text-xs text-primary-600 hover:text-primary-800 whitespace-nowrap">Enregistrer</button>
                                        <button type="button" @click="editing = false" class="text-xs text-gray-400 hover:text-gray-600 whitespace-nowrap">Annuler</button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-400 text-center py-6">Aucun site. Une entreprise peut avoir plusieurs sites (sièges, usines, points de vente...).</p>
                            @endif
                        </div>
                    </div>

                    {{-- Contrats --}}
                    <div x-show="tab === 'contracts'" x-cloak>
                        <div class="bg-white shadow-card rounded-2xl p-5">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm font-medium text-gray-700">Contrats d'assurance</span>
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-sm text-gray-800 font-medium hover:text-gray-600">+ Ajouter</button>
                                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-10 text-left">
                                        <form method="POST" action="{{ route('clients.contracts.store', $client) }}" class="space-y-3">
                                            @csrf
                                            <select name="insurer_id" required class="block w-full rounded-lg border-gray-300 text-sm">
                                                <option value="">Assureur *</option>
                                                @foreach ($insurers as $insurer)
                                                    <option value="{{ $insurer->id }}">{{ $insurer->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="0.01" min="0" max="100" name="coverage_rate" placeholder="Taux de couverture (%) *" required class="block w-full rounded-lg border-gray-300 text-sm">
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="date" name="start_date" required class="block w-full rounded-lg border-gray-300 text-sm">
                                                <input type="date" name="end_date" class="block w-full rounded-lg border-gray-300 text-sm">
                                            </div>
                                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                                                Contrat actif
                                            </label>
                                            <div class="flex gap-2 pt-1">
                                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1.5 rounded-lg">Ajouter</button>
                                                <button type="button" @click="open = false" class="text-sm text-gray-400 hover:text-gray-600 px-3 py-1.5">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if ($client->insuranceContracts->count())
                            <div class="relative mb-4">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" data-list-filter data-target="#contracts-list"
                                    placeholder="Rechercher un contrat (n° ou assureur)..."
                                    class="w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                            <div id="contracts-list" class="divide-y divide-gray-100">
                                @foreach ($client->insuranceContracts as $contract)
                                <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0" x-data="{ editOpen: false }"
                                    data-search="{{ mb_strtolower($contract->contract_number . ' ' . $contract->insurer->name) }}">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $contract->insurer->name }} · {{ $contract->coverage_rate }}% couverture
                                            · depuis le {{ $contract->start_date->format('d/m/Y') }}
                                            @if ($contract->end_date) jusqu'au {{ $contract->end_date->format('d/m/Y') }} @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 relative">
                                        <span class="text-xs px-2 py-0.5 rounded {{ $contract->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                            {{ $contract->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                        <button @click="editOpen = !editOpen" class="text-xs text-gray-400 hover:text-gray-700">Modifier</button>
                                        <form method="POST" action="{{ route('clients.contracts.destroy', [$client, $contract]) }}" onsubmit="return confirm('Supprimer ce contrat ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Supprimer</button>
                                        </form>

                                        <div x-show="editOpen" x-cloak @click.outside="editOpen = false" class="absolute right-0 top-6 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-10 text-left">
                                            <form method="POST" action="{{ route('clients.contracts.update', [$client, $contract]) }}" class="space-y-3">
                                                @csrf @method('PUT')
                                                <select name="insurer_id" required class="block w-full rounded-lg border-gray-300 text-sm">
                                                    @foreach ($insurers as $insurer)
                                                        <option value="{{ $insurer->id }}" @selected($insurer->id === $contract->insurer_id)>{{ $insurer->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="number" step="0.01" min="0" max="100" name="coverage_rate" value="{{ $contract->coverage_rate }}" required class="block w-full rounded-lg border-gray-300 text-sm">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="date" name="start_date" value="{{ $contract->start_date->format('Y-m-d') }}" required class="block w-full rounded-lg border-gray-300 text-sm">
                                                    <input type="date" name="end_date" value="{{ optional($contract->end_date)->format('Y-m-d') }}" class="block w-full rounded-lg border-gray-300 text-sm">
                                                </div>
                                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                                    <input type="checkbox" name="is_active" value="1" @checked($contract->is_active) class="rounded border-gray-300">
                                                    Contrat actif
                                                </label>
                                                <div class="flex gap-2 pt-1">
                                                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1.5 rounded-lg">Enregistrer</button>
                                                    <button type="button" @click="editOpen = false" class="text-sm text-gray-400 hover:text-gray-600 px-3 py-1.5">Annuler</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p id="contracts-no-match" class="hidden text-sm text-gray-400 text-center py-6">Aucun contrat ne correspond à la recherche.</p>
                            @else
                            <p class="text-sm text-gray-400 text-center py-6">Aucun contrat d'assurance.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-list-filter]').forEach(input => {
            const target = input.dataset.target.replace('#', '');
            input.addEventListener('input', () => {
                let shown = 0;
                document.querySelectorAll('#' + target + ' [data-search]').forEach(el => {
                    const hit = !input.value || el.dataset.search.includes(input.value.toLowerCase());
                    el.style.display = hit ? '' : 'none';
                    if (hit) shown++;
                });
                const no = document.getElementById(target + '-no-match');
                if (no) no.classList.toggle('hidden', shown > 0);
            });
        });
    </script>
</x-app-layout>
