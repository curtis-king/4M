<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Calendrier des visites — {{ $startOfMonth->format('F Y') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('visits.calendar', ['month' => $startOfMonth->subMonth()->format('Y-m')]) }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-3 py-1.5 rounded-lg">&larr; Préc.</a>
                <a href="{{ route('visits.calendar', ['month' => $startOfMonth->addMonth()->format('Y-m')]) }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-3 py-1.5 rounded-lg">Suiv. &rarr;</a>
                <a href="{{ route('visits.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg">+ Nouvelle</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $start = $startOfMonth->copy()->startOfMonth();
                $end = $startOfMonth->copy()->endOfMonth();
                $dayOfWeek = (int) $start->format('N');
            @endphp

            <div class="bg-white shadow-card rounded-2xl p-6">
                <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200">
                    @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                        <div class="bg-gray-50 text-center text-xs font-semibold text-gray-500 py-2">{{ $day }}</div>
                    @endforeach

                    @for ($i = 1; $i < $dayOfWeek; $i++)
                        <div class="bg-gray-50 min-h-[80px] opacity-40"></div>
                    @endfor

                    @for ($day = 1; $day <= $end->day; $day++)
                        @php
                            $date = $start->copy()->addDays($day - 1)->format('Y-m-d');
                            $dayVisits = $visits->get($date, collect());
                        @endphp
                        <div class="bg-white min-h-[80px] p-1 text-sm">
                            <div class="font-medium text-gray-700 text-xs mb-1">{{ $day }}</div>
                            @foreach ($dayVisits as $visit)
                                <a href="{{ route('visits.show', $visit) }}"
                                    class="block text-xs rounded px-1 py-0.5 mb-0.5 truncate
                                        {{ $visit->status === 'realisee' ? 'bg-green-100 text-green-700' :
                                           ($visit->status === 'planifiee' ? 'bg-blue-100 text-blue-700' :
                                           ($visit->status === 'absent' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                                    {{ $visit->client->name }}
                                </a>
                            @endforeach
                        </div>
                    @endfor

                    @php $remaining = 7 - (($dayOfWeek - 1) + $end->day) % 7; @endphp
                    @if ($remaining < 7)
                        @for ($i = 0; $i < $remaining; $i++)
                            <div class="bg-gray-50 min-h-[80px] opacity-40"></div>
                        @endfor
                    @endif
                </div>

                <div class="mt-4 flex gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-100 rounded"></span> Planifiée</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-100 rounded"></span> Réalisée</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-yellow-100 rounded"></span> Absent</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-100 rounded"></span> Annulée</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
