{{-- Overlay mobile --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" x-transition.opacity
     class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"></div>

<aside x-cloak
       @mouseenter="sidebarHover = true" @mouseleave="sidebarHover = false"
       class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full transform flex-col border-r border-gray-100 bg-white transition-all duration-200 ease-in-out lg:static lg:translate-x-0"
       :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarHover ? 'lg:w-72' : 'lg:w-20']">

    <div class="flex h-16 shrink-0 items-center gap-3 overflow-hidden border-b border-gray-100 px-6"
         :class="!sidebarHover && 'lg:px-0 lg:justify-center'">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-sm font-bold text-white">4M</div>
        <div class="leading-tight transition-all duration-150" :class="!sidebarHover && 'lg:hidden'">
            <p class="whitespace-nowrap text-sm font-semibold text-gray-900">Labo 4M</p>
            <p class="whitespace-nowrap text-xs text-gray-400">Facturation</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
        <a href="{{ route('dashboard') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V19.5a1.5 1.5 0 01-1.5 1.5h-3.75a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75v4.5a.75.75 0 01-.75.75H4.5A1.5 1.5 0 013 19.5V9.75z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Dashboard') }}</span>
        </a>

        @canany(['view clients', 'create client'])
        <a href="{{ route('clients.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('clients.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Clients') }}</span>
        </a>
        @endcanany

        @canany(['view invoices', 'create invoice'])
        <a href="{{ route('invoices.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('invoices.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Factures') }}</span>
        </a>
        @endcanany

        @canany(['manage payments'])
        <a href="{{ route('payments.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('payments.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Paiements') }}</span>
        </a>
        @endcanany

        @canany(['view visits', 'create visit'])
        <a href="{{ route('visits.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('visits.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Visites') }}</span>
        </a>
        @endcanany

        @canany(['manage services'])
        <a href="{{ route('services.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('services.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5.106 14.4c-1.298 1.298-.375 3.6 1.591 3.6h10.606c1.966 0 2.889-2.302 1.591-3.6l-3.985-3.99a2.25 2.25 0 01-.659-1.591V3.104M9.75 3.104A24.301 24.301 0 0112 3c1.152 0 2.286.116 3.375.328M14.25 3.104v5.714c0 .597.237 1.17.659 1.591L15 10.5m0 0l.399.399M15 10.5H9" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Services') }}</span>
        </a>
        @endcanany

        @can('manage insurers')
        <a href="{{ route('assureurs.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('assureurs.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Assureurs') }}</span>
        </a>
        @endcan

        @canany(['manage reagents'])
        <a href="{{ route('reagents.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('reagents.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Réactifs') }}</span>
        </a>
        @endcanany

        @can('manage users')
        <a href="{{ route('users.index') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Utilisateurs') }}</span>
        </a>
        @endcan

        @can('manage settings')
        <a href="{{ route('settings.edit') }}"
           :class="!sidebarHover && 'lg:justify-center lg:px-0'"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('settings.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="whitespace-nowrap" :class="!sidebarHover && 'lg:hidden'">{{ __('Paramètres') }}</span>
        </a>
        @endcan
    </nav>

    <div class="border-t border-gray-100 p-4">
        <x-dropdown align="left" width="w-60">
            <x-slot name="trigger">
                <button class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition-colors hover:bg-gray-50"
                        :class="!sidebarHover && 'lg:justify-center'">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                        {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                    </div>
                    <div class="min-w-0 flex-1 leading-tight" :class="!sidebarHover && 'lg:hidden'">
                        <p class="truncate text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-gray-400">{{ Auth::user()->email }}</p>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" :class="!sidebarHover && 'lg:hidden'">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</aside>
