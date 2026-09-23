<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $company = \App\Models\CompanySetting::instance();
        $hasLogo = file_exists(public_path('img/logo.png'));
        $appName = $company->name ?: config('app.name', "CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE");
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $appName }}</title>

        @if ($hasLogo)
            <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}" />
            <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}" />
        @endif

        @fonts

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-900 antialiased">

        {{-- Navbar --}}
        <header class="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    @if ($hasLogo)
                        <img src="{{ asset('img/logo.png') }}" alt="{{ $appName }}" class="h-9 w-9 shrink-0 rounded-xl object-contain">
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-600 text-sm font-bold text-white">4M</div>
                    @endif
                    <span class="leading-tight">
                        <span class="block text-xs font-semibold text-gray-900">{{ $appName }}</span>
                        <span class="block text-xs text-gray-400">Facturation</span>
                    </span>
                    @if ($hasLogo)
                        <img src="{{ asset('img/ROUGE.png') }}" alt="{{ $appName }}" class="h-9 w-9 shrink-0 rounded-xl object-contain">
                    @endif
                </a>

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700">
                        Se connecter
                    </a>
                @endauth
            </div>
        </header>

        {{-- Hero --}}
        <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 via-white to-white">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="mb-6 flex items-center justify-center gap-4">
                        @if ($hasLogo)
                            <img src="{{ asset('img/logo.png') }}" alt="{{ $appName }}" class="h-16 w-16 rounded-2xl object-contain shadow-card">
                            <img src="{{ asset('img/ROUGE.png') }}" alt="{{ $appName }}" class="h-16 w-16 rounded-2xl object-contain shadow-card">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-600 text-2xl font-bold text-white shadow-card">4M</div>
                        @endif
                    </div>

                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
                        Votre laboratoire d'analyses,
                        <span class="text-primary-600">facturation simplifiée</span>
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Gérez vos clients, vos factures, vos visites et vos assureurs au même endroit.
                        Rapide, fiable et pensé pour votre quotidien.
                    </p>

                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <a href="{{ route('login') }}"
                           class="inline-flex w-full items-center justify-center rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700 sm:w-auto">
                            Se connecter à mon espace
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-colors hover:bg-gray-50 sm:w-auto">
                                Aller au tableau de bord
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 sm:w-auto">
                                J'ai déjà un compte
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        {{-- Points forts --}}
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900">Tout votre laboratoire, un seul outil</h2>
                <p class="mt-3 text-gray-600">Des modules pensés pour simplifier la gestion au quotidien.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-soft transition-shadow hover:shadow-card">
                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5'>
                            <path stroke-linecap='round' stroke-linejoin='round' d='M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Facturation</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Créez et suivez vos factures, encaissements et certifications en quelques clics.</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-soft transition-shadow hover:shadow-card">
                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5'>
                            <path stroke-linecap='round' stroke-linejoin='round' d='M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Clients</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Centralisez vos clients, leurs fiches fiscales et leur historique complet.</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-soft transition-shadow hover:shadow-card">
                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5'>
                            <path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z' />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Assureurs</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Gérez les conventions, les remises et le suivi de vos compagnies d'assurance.</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-soft transition-shadow hover:shadow-card">
                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5'>
                            <path stroke-linecap='round' stroke-linejoin='round' d='M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' />
                            <path stroke-linecap='round' stroke-linejoin='round' d='M9.75 12.75l3 3 5.25-6' />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Suivi</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Visites, réactifs et paiements : gardez le contrôle sur toute votre activité.</p>
                </div>
            </div>
        </section>

        {{-- CTA bandeau --}}
        <section class="border-y border-gray-100 bg-primary-50">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 px-4 py-12 text-center sm:px-6 lg:flex-row lg:text-left">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Prêt à simplifier votre facturation ?</h2>
                    <p class="mt-2 text-gray-600">Connectez-vous et retrouvez votre tableau de bord en un clic.</p>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex shrink-0 items-center justify-center rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700">
                        Ouvrir le tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex shrink-0 items-center justify-center rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700">
                        Se connecter
                    </a>
                @endauth
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-white">
            <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        @if ($hasLogo)
                            <img src="{{ asset('img/logo.png') }}" alt="{{ $appName }}" class="h-8 w-8 shrink-0 rounded-lg object-contain">
                        @else
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-xs font-bold text-white">4M</div>
                        @endif
                        <div class="leading-tight">
                            <p class="text-xs font-semibold text-gray-900">{{ $appName }}</p>
                            <p class="text-xs text-gray-400">Facturation &amp; gestion de laboratoire</p>
                        </div>
                        @if ($hasLogo)
                            <img src="{{ asset('img/ROUGE.png') }}" alt="{{ $appName }}" class="h-8 w-8 shrink-0 rounded-lg object-contain">
                        @endif
                    </div>

                    <div class="flex flex-col items-center gap-1 text-center text-xs text-gray-500 sm:items-end sm:text-right">
                        @if ($company->address)
                            <span>{{ $company->address }}</span>
                        @endif
                        @if ($company->phone)
                            <span>Tél. : {{ $company->phone }}</span>
                        @endif
                        @if ($company->email)
                            <span>{{ $company->email }}</span>
                        @endif
                        @unless ($company->address || $company->phone || $company->email)
                            <span>© {{ date('Y') }} {{ $appName }} — Tous droits réservés</span>
                        @endunless
                    </div>
                </div>
                <div class="mt-8 flex justify-center border-t border-gray-100 pt-6">
                    <img src="{{ asset('img/iso 9001.jpg') }}" alt="Certification ISO 9001" class="h-28 w-auto object-contain">
                </div>
            </div>
        </footer>
    </body>
</html>