<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Insurer;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StatementService
{
    public function companies(Insurer $insurer): Collection
    {
        $clientIds = $insurer->contracts()->pluck('client_id');

        return Client::whereIn('id', $clientIds)
            ->whereIn('type', ['entreprise', 'particulier'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);
    }

    public function options(Insurer $insurer, string|int|null $companyId, ?string $month): array
    {
        [$start, $end] = $this->resolvePeriod($month);

        $companies = $this->companies($insurer)
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
            ]);

        $result = [
            'companies' => $companies,
            'month' => Carbon::parse($start)->format('Y-m'),
        ];

        if (!$companyId) {
            return $result;
        }

        $scopeAll = $companyId === 'all';

        $query = Visit::with(['client', 'agent', 'agents', 'insuranceContract'])
            ->withCount('agents')
            ->where('status', 'realisee')
            ->whereBetween('visit_date', [$start, $end])
            ->whereDoesntHave('statementItems');

        $contract = null;
        if ($scopeAll) {
            $query->whereHas('insuranceContract', fn ($q) => $q->where('insurer_id', $insurer->id));
        } else {
            $contract = $insurer->contracts()->where('client_id', (int) $companyId)->first();

            if (!$contract) {
                return $result + ['agents' => [], 'empty_reason' => 'Aucun contrat relié.'];
            }

            $query->where('insurance_contract_id', $contract->id);
        }

        $visits = $query->orderBy('visit_date')->orderBy('id')->get();

        $grouped = $visits->groupBy(fn ($v) => $v->agents_count > 1
            ? 'c'.$v->client_id
            : ($v->agent_id !== null ? 'a'.$v->agent_id : 'c'.$v->client_id));

        $agents = [];
        foreach ($grouped as $key => $group) {
            $first = $group->first();
            $items = $group->map(fn ($v) => [
                'id' => $v->id,
                'visit_date' => $v->visit_date->format('d/m/Y'),
                'objet' => $v->invoice_label,
                'assured_name' => $v->assured_name,
                'company_name' => $v->company_name,
                'coverage_rate' => (float) ($v->insuranceContract?->coverage_rate ?? 0),
                'subtotal' => (float) $v->subtotal,
                'discount_amount' => (float) $v->discount_amount,
                'total' => (float) $v->total,
                'insurance_covered' => (float) $v->insurance_covered,
            ]);

            $isCompany = str_starts_with($key, 'c');
            $name = $isCompany || !$first->agent_id
                ? ($first->client->name ?? 'Assuré')
                : ($first->agent?->name ?: ($first->client->name ?? 'Assuré'));

            if ($scopeAll && !$isCompany && $first->client_id) {
                $name = ($first->client->name ?? '').' — '.$name;
            }

            $agents[] = [
                'id' => $isCompany ? null : $first->agent_id,
                'name' => $name,
                'visits' => $items,
                'subtotal' => round($items->sum('total'), 2),
                'insurance_total' => round($items->sum('insurance_covered'), 2),
            ];
        }

        $result['agents'] = $agents;
        if ($contract) {
            $result['contract'] = [
                'id' => $contract->id,
                'coverage_rate' => (float) $contract->coverage_rate,
                'client_id' => $contract->client_id,
                'client_name' => $contract->client?->name,
            ];
        }
        $result['grand_total'] = round($visits->sum('total'), 2);
        $result['grand_insurance'] = round($visits->sum('insurance_covered'), 2);

        return $result;
    }

    public function create(Insurer $insurer, array $visitIds, ?string $month, float|int $discountValue, array $mission = []): Invoice
    {
        if (empty($visitIds)) {
            throw new \InvalidArgumentException('Aucune visite sélectionnée.');
        }

        [$start, $end] = $this->resolvePeriod($month);

        $visits = Visit::with(['client', 'agent', 'agents', 'insuranceContract', 'examLines.service'])
            ->whereIn('id', $visitIds)
            ->whereHas('insuranceContract', fn ($q) => $q->where('insurer_id', $insurer->id))
            ->where('status', 'realisee')
            ->whereBetween('visit_date', [$start, $end])
            ->whereDoesntHave('statementItems')
            ->orderBy('visit_date')
            ->orderBy('id')
            ->get();

        if ($visits->isEmpty()) {
            throw new \InvalidArgumentException('Aucune visite éligible pour la période donnée.');
        }

        if ($visits->count() !== count($visitIds)) {
            $found = $visits->pluck('id')->all();
            $missing = array_values(array_diff($visitIds, $found));
            if ($missing) {
                throw new \InvalidArgumentException('Certaines visites sélectionnées ne sont plus éligibles (déjà facturées ou hors période).');
            }
        }

        $discountValue = max(0, (float) $discountValue);

        return DB::transaction(function () use ($insurer, $visits, $start, $end, $discountValue, $mission) {
            $invoice = Invoice::create([
                'client_id' => $insurer->id,
                'created_by' => auth()->id() ?? User::first()?->id,
                'recipient_type' => 'business',
                'date' => $end,
                'due_date' => Carbon::parse($end)->addDays(30)->toDateString(),
                'status' => 'brouillon',
                'currency' => 'XAF',
                'is_statement' => true,
                'statement_start_date' => $start,
                'statement_end_date' => $end,
                'subject' => $mission['subject'] ?? null,
                'sample_nature' => $mission['sample_nature'] ?? null,
                'company_site' => $mission['company_site'] ?? null,
                'tax_rate' => 0,
                'discount_type' => $discountValue > 0 ? 'pourcentage' : 'aucun',
                'discount_value' => $discountValue,
                'notes' => sprintf(
                    'Facture de sommation d\'assurance — période du %s au %s. Remise négociée : %s%s.',
                    Carbon::parse($start)->format('d/m/Y'),
                    Carbon::parse($end)->format('d/m/Y'),
                    $discountValue > 0 ? number_format($discountValue, 1).' %' : 'aucune',
                    ''
                ),
            ]);

            foreach ($visits as $visit) {
                $invoice->items()->create([
                    'source_visit_id' => $visit->id,
                    'service_id' => $visit->examLines->first()?->service_id,
                    'description' => $visit->invoice_label,
                    'type' => 'analyse',
                    'item_type' => 'service',
                    'quantity' => 1,
                    'unit_price' => $visit->subtotal,
                    'discount_type' => $visit->discount_amount > 0 ? 'montant' : 'aucun',
                    'discount_value' => $visit->discount_amount,
                    'company_name' => $visit->company_name,
                    'assured_name' => $visit->assured_name,
                    'coverage_rate' => $visit->insuranceContract?->coverage_rate,
                    'insurance_part' => $visit->insurance_covered,
                ]);
            }

            $invoice->recalculate();

            return $invoice->refresh();
        });
    }

    protected function resolvePeriod(?string $month): array
    {
        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $start = Carbon::parse($month.'-01')->startOfMonth();

        return [
            $start->toDateString(),
            $start->copy()->endOfMonth()->toDateString(),
        ];
    }
}