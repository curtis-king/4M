@php
    $lowStockCount = \App\Models\Reagent::whereColumn('quantity', '<=', 'min_quantity')->count();
@endphp

<header class="flex h-16 shrink-0 items-center gap-4 border-b border-gray-100 bg-white px-4 sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <div class="min-w-0 flex-1">
        @isset($header)
            {{ $header }}
        @endisset
    </div>

    <div class="hidden items-center md:flex md:w-64 lg:w-80" x-data="topbarSearch()" @click.outside="open = false">
        <div class="relative w-full">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="search" x-model="q" @input.debounce.300ms="search()" @focus="if (results) open = true"
                   placeholder="Rechercher un client, une facture..."
                   class="w-full rounded-xl border-0 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-primary-100" />

            <div x-show="open" x-cloak x-transition
                 class="absolute left-0 right-0 z-50 mt-2 max-h-96 overflow-auto rounded-2xl border border-gray-100 bg-white p-2 shadow-card">
                <template x-if="results && results.clients.length === 0 && results.invoices.length === 0">
                    <p class="px-3 py-2 text-sm text-gray-400">Aucun résultat.</p>
                </template>

                <template x-if="results && results.clients.length > 0">
                    <div>
                        <p class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">Clients</p>
                        <template x-for="c in results.clients" :key="'c' + c.id">
                            <a :href="c.url" class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm hover:bg-gray-50">
                                <span class="truncate text-gray-900" x-text="c.name"></span>
                                <span class="shrink-0 text-xs text-gray-400" x-text="c.phone"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <template x-if="results && results.invoices.length > 0">
                    <div>
                        <p class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">Factures</p>
                        <template x-for="i in results.invoices" :key="'i' + i.id">
                            <a :href="i.url" class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm hover:bg-gray-50">
                                <span class="truncate text-gray-900" x-text="i.number + ' — ' + i.client"></span>
                                <span class="shrink-0 text-xs text-gray-400" x-text="i.total + ' FCFA'"></span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" @click.outside="open = false" class="relative rounded-xl p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            @if ($lowStockCount > 0)
                <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-semibold text-white">
                    {{ $lowStockCount > 9 ? '9+' : $lowStockCount }}
                </span>
            @endif
        </button>

        <div x-show="open" x-cloak x-transition
             class="absolute right-0 z-50 mt-2 w-72 rounded-2xl border border-gray-100 bg-white p-2 shadow-card">
            <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Notifications</p>
            @if ($lowStockCount > 0)
                <a href="{{ route('reagents.index') }}" class="flex items-start gap-3 rounded-xl px-3 py-2.5 text-sm hover:bg-gray-50">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    <span class="text-gray-600">
                        <span class="font-medium text-gray-900">{{ $lowStockCount }}</span>
                        réactif(s) en stock bas nécessitent un réapprovisionnement.
                    </span>
                </a>
            @else
                <p class="px-3 py-2 text-sm text-gray-400">Aucune nouvelle notification.</p>
            @endif
        </div>
    </div>

    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-xl p-1.5 hover:bg-gray-50">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700">
                {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
            </div>
            <svg class="hidden h-4 w-4 text-gray-400 sm:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>

        <div x-show="open" x-cloak x-transition
             class="absolute right-0 z-50 mt-2 w-60 rounded-2xl border border-gray-100 bg-white p-2 shadow-card">
            <div class="px-3 py-2 border-b border-gray-100 mb-1">
                <p class="truncate text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-gray-400">{{ Auth::user()->email }}</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    function topbarSearch() {
        return {
            q: '',
            results: null,
            open: false,
            search() {
                if (this.q.trim().length < 2) {
                    this.results = null;
                    this.open = false;
                    return;
                }
                fetch('{{ route('search') }}?q=' + encodeURIComponent(this.q))
                    .then(r => r.json())
                    .then(data => {
                        this.results = data;
                        this.open = true;
                    });
            },
        }
    }
</script>
