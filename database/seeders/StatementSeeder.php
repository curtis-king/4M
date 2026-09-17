<?php

namespace Database\Seeders;

use App\Models\Insurer;
use App\Models\Visit;
use App\Services\StatementService;
use Illuminate\Database\Seeder;

class StatementSeeder extends Seeder
{
    public function run(): void
    {
        $sgam = Insurer::where('name', 'SGAM')->first();

        if (!$sgam) {
            return;
        }

        $month = now()->format('Y-m');

        $sgamVisits = Visit::whereHas('insuranceContract', fn ($q) => $q->where('insurer_id', $sgam->id))
            ->where('status', 'realisee')
            ->whereMonth('visit_date', now()->month)
            ->whereYear('visit_date', now()->year)
            ->whereDoesntHave('statementItems')
            ->pluck('id')
            ->all();

        if ($sgamVisits) {
            app(StatementService::class)->create($sgam, $sgamVisits, $month, $sgam->discount_rate > 0 ? (float) $sgam->discount_rate : 15.0);
        }
    }
}