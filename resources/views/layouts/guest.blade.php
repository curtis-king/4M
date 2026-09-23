<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen">

        {{-- Panneau de marque (laboratoire) --}}
        <div class="relative hidden w-[44%] flex-col justify-between overflow-hidden bg-gradient-to-br from-primary-800 via-primary-700 to-teal-800 p-10 text-white lg:flex xl:p-14">

            {{-- Décor : cercles flous --}}
            <div class="pointer-events-none absolute -left-16 -top-16 h-72 w-72 rounded-full bg-teal-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 right-0 h-96 w-96 rounded-full bg-primary-400/20 blur-3xl"></div>

            {{-- Décor : grille de points --}}
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.15]" aria-hidden="true">
                <defs>
                    <pattern id="dot-grid" width="22" height="22" patternUnits="userSpaceOnUse">
                        <circle cx="1.5" cy="1.5" r="1.5" fill="white" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#dot-grid)" />
            </svg>

            {{-- Logo --}}
            <div class="relative z-10 flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-soft">
                    <img src="{{ asset('img/logo.png') }}" alt="CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE" class="h-full w-full object-contain">
                </div>
                <span class="text-sm font-semibold leading-tight tracking-tight sm:text-base">CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE</span>
                <div class="ml-auto flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-soft">
                    <img src="{{ asset('img/ROUGE.png') }}" alt="Logo 4M" class="h-full w-full object-contain">
                </div>
            </div>

            {{-- Illustration : badges d'icônes laboratoire --}}
            <div class="relative z-10 grid grid-cols-2 gap-5 py-10">
                <div class="flex h-20 w-20 rotate-[-4deg] items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15">
                    {{-- Éprouvette --}}
                    <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5h6M10 3v12a3 3 0 006 0V3" />
                        <path stroke-linecap="round" d="M10.5 11.5h5" />
                    </svg>
                </div>
                <div class="mt-8 flex h-20 w-20 rotate-[3deg] items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15">
                    {{-- ADN --}}
                    <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" d="M7 3c0 4 10 4 10 8s-10 4-10 8M17 3c0 4-10 4-10 8s10 4 10 8" />
                        <path stroke-linecap="round" d="M8 7h8M8 12h8M7.5 17h9" />
                    </svg>
                </div>
                <div class="flex h-20 w-20 rotate-[2deg] items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15">
                    {{-- Ballon d'analyse --}}
                    <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v5.5L5.5 17a2 2 0 001.8 3h9.4a2 2 0 001.8-3L14 8.5V3" />
                        <path stroke-linecap="round" d="M8 15h8" />
                    </svg>
                </div>
                <div class="mt-8 flex h-20 w-20 rotate-[-3deg] items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15">
                    {{-- Certification --}}
                    <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 3h5l4 4v13a1 1 0 01-1 1H8a1 1 0 01-1-1V4a1 1 0 011-1z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 3v4h4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4" />
                    </svg>
                </div>
            </div>

            {{-- Texte de marque --}}
            <div class="relative z-10">
                <h1 class="text-2xl font-bold leading-snug xl:text-3xl">
                    La gestion de votre<br>laboratoire, simplifiée.
                </h1>
                <p class="mt-3 max-w-sm text-sm text-white/70">
                    Facturation, suivi des analyses et certification électronique réunis dans un seul espace.
                </p>
                <ul class="mt-6 space-y-2.5 text-sm text-white/85">
                    <li class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Facturation certifiée SFEC
                    </li>
                    <li class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Suivi des visites, examens et résultats
                    </li>
                    <li class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Gestion multi-sites, assurances et paiements
                    </li>
                </ul>
            </div>
        </div>

        {{-- Panneau formulaire --}}
        <div class="flex flex-1 flex-col items-center justify-center bg-gray-50 px-6 py-12">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-soft">
                    <img src="{{ asset('img/logo.png') }}" alt="CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE" class="h-full w-full object-contain">
                </div>
                <span class="text-xs font-semibold uppercase leading-tight tracking-tight text-gray-900">CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE</span>
                <div class="ml-auto flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-soft">
                    <img src="{{ asset('img/ROUGE.png') }}" alt="Logo 4M" class="h-full w-full object-contain">
                </div>
            </div>

            <div class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-card">
                {{ $slot }}
            </div>

            <div class="mt-6 flex flex-col items-center gap-2">
                <img src="{{ asset('img/iso 9001.jpg') }}" alt="Certification ISO 9001" class="h-16 w-auto object-contain">
                <p class="text-center text-xs text-gray-400">
                    © {{ date('Y') }} CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE — Laboratoire d'analyses médicales
                </p>
            </div>
        </div>
    </div>
</body>

</html>
