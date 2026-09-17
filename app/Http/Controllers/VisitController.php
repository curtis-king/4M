<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\InsuranceContract;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::with(['client', 'agent', 'agents', 'insuranceContract.insurer'])
            ->withCount('examLines');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('objet', 'like', $search)
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', $search))
                  ->orWhereHas('agent', fn ($aq) => $aq->where('name', 'like', $search))
                  ->orWhereHas('agents', fn ($aq) => $aq->where('name', 'like', $search));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->date_to);
        }

        $visits = $query->latest('visit_date')->paginate(15);

        return view('visits.index', compact('visits'));
    }

    public function create()
    {
        $clients = Client::whereIn('type', ['entreprise', 'particulier'])->orderBy('name')->get();
        $services = Service::with('category')->where('is_active', true)->orderBy(ServiceCategory::select('sort_order')->whereColumn('service_categories.id', 'services.category_id'))->orderBy('name')->get();
        $serviceOptions = $this->servicePayload($services);

        return view('visits.create', compact('clients', 'services', 'serviceOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateVisit($request);

        $this->normalizeDiscount($validated);

        $examItems = $validated['exam_items'] ?? [];
        unset($validated['exam_items']);

        if ($validated['insurance_contract_id']) {
            $clientId = $validated['client_id'];
            $contract = InsuranceContract::find($validated['insurance_contract_id']);

            if (!$contract || $contract->client_id != $clientId || $contract->insurer_id == $clientId) {
                return back()->withErrors(['insurance_contract_id' => 'Ce contrat d\'assurance ne correspond pas au client sélectionné.'])->withInput();
            }
        }

        $agentIds = $validated['agent_ids'] ?? [];
        if ($agentIds && !$this->agentsBelongToClient($agentIds, $validated['client_id'])) {
            return back()->withErrors(['agent_ids' => 'Certains employés sélectionnés ne font pas partie de cette société.'])->withInput();
        }
        $this->normalizeAgents($validated, $agentIds);

        $visit = Visit::create($validated);

        $this->syncAgents($visit, $agentIds);

        $this->syncExamLines($visit, $examItems);

        $visit->recalculate();

        return redirect()->route('visits.show', $visit)->with('success', 'Visite créée avec succès.');
    }

    public function show(Visit $visit)
    {
        $visit->load([
            'client',
            'agent',
            'agents',
            'insuranceContract.insurer',
            'examLines.service',
            'statementItems.invoice' => fn ($q) => $q->where('is_statement', true),
        ]);

        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit)
    {
        $visit->load(['client', 'agent', 'agents', 'insuranceContract.insurer', 'examLines.service']);

        $clients = Client::whereIn('type', ['entreprise', 'particulier'])->orderBy('name')->get();
        $agents = Agent::where('client_id', $visit->client_id)->orderBy('name')->get();
        $services = Service::with('category')->where('is_active', true)->orderBy(ServiceCategory::select('sort_order')->whereColumn('service_categories.id', 'services.category_id'))->orderBy('name')->get();
        $serviceOptions = $this->servicePayload($services);

        return view('visits.edit', compact('visit', 'clients', 'agents', 'services', 'serviceOptions'));
    }

    public function update(Request $request, Visit $visit)
    {
        if ($visit->isBilled()) {
            return redirect()->route('visits.show', $visit)
                ->with('error', 'Cette visite a déjà été facturée dans une facture de sommation. Elle ne peut plus être modifiée.');
        }

        $validated = $this->validateVisit($request);

        $this->normalizeDiscount($validated);

        $examItems = $validated['exam_items'] ?? [];
        unset($validated['exam_items']);

        if ($validated['insurance_contract_id']) {
            $contract = InsuranceContract::find($validated['insurance_contract_id']);

            if (!$contract || $contract->client_id != $validated['client_id'] || $contract->insurer_id == $validated['client_id']) {
                return back()->withErrors(['insurance_contract_id' => 'Ce contrat d\'assurance ne correspond pas au client sélectionné.'])->withInput();
            }
        }

        $agentIds = $validated['agent_ids'] ?? [];
        if ($agentIds && !$this->agentsBelongToClient($agentIds, $validated['client_id'])) {
            return back()->withErrors(['agent_ids' => 'Certains employés sélectionnés ne font pas partie de cette société.'])->withInput();
        }
        $this->normalizeAgents($validated, $agentIds);

        $visit->update($validated);
        $this->syncAgents($visit, $agentIds);
        $this->syncExamLines($visit, $examItems);
        $visit->recalculate();

        return redirect()->route('visits.show', $visit)->with('success', 'Visite mise à jour.');
    }

    public function destroy(Visit $visit)
    {
        if ($visit->isBilled()) {
            return back()->with('error', 'Cette visite a déjà été facturée dans une facture de sommation et ne peut pas être supprimée.');
        }

        $visit->delete();

        return redirect()->route('visits.index')->with('success', 'Visite supprimée.');
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $visits = Visit::with(['client', 'agent'])
            ->whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(fn ($v) => $v->visit_date->format('Y-m-d'));

        return view('visits.calendar', compact('visits', 'startOfMonth', 'endOfMonth'));
    }

    protected function validateVisit(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'nullable|exists:agents,id',
            'beneficiary_name' => 'nullable|string|max:255',
            'objet' => 'nullable|string|max:255',
            'produit_alimentaire' => 'nullable|string|max:255',
            'numero_lot' => 'nullable|string|max:100',
            'origine_produit' => 'nullable|string|max:255',
            'date_prelevement' => 'nullable|date',
            'type_controle' => 'nullable|in:routine,surveillance,plainte,certification',
            'statut_resultat' => 'nullable|in:en_attente,conforme,non_conforme',
            'insurance_contract_id' => 'nullable|exists:insurance_contracts,id',
            'visit_date' => 'required|date',
            'status' => 'required|in:planifiee,realisee,annulee,absent',
            'notes' => 'nullable|string',
            'patient_paid' => 'nullable|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0|max:100',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'integer|exists:agents,id',
            'exam_items' => 'nullable|array',
            'exam_items.*.service_id' => 'required|exists:services,id',
            'exam_items.*.quantity' => 'required|numeric|min:0.01',
            'exam_items.*.unit_price' => 'required|numeric|min:0',
            'exam_items.*.discount_type' => 'required|in:aucun,pourcentage,montant',
            'exam_items.*.discount_value' => 'required|numeric|min:0',
            'exam_items.*.result' => 'nullable|string',
            'exam_items.*.resultat_valeur' => 'nullable|string|max:255',
            'exam_items.*.unite_mesure' => 'nullable|string|max:50',
            'exam_items.*.valeur_limite' => 'nullable|string|max:255',
            'exam_items.*.est_conforme' => 'nullable|boolean',
        ]);
    }

    protected function normalizeDiscount(array &$validated): void
    {
        $value = (float) ($validated['discount_value'] ?? 0);
        $validated['discount_type'] = $value > 0 ? 'pourcentage' : 'aucun';
        $validated['discount_value'] = $value;
    }

    protected function agentsBelongToClient(array $agentIds, int $clientId): bool
    {
        $ids = array_values(array_unique(array_map('intval', $agentIds)));

        return Agent::where('client_id', $clientId)->whereIn('id', $ids)->count() === count($ids);
    }

    protected function normalizeAgents(array &$validated, array $agentIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $agentIds)));

        $validated['agent_id'] = $ids[0] ?? null;
        unset($validated['agent_ids']);
    }

    protected function syncAgents(Visit $visit, array $agentIds): void
    {
        $ids = array_unique(array_map('intval', $agentIds));

        if (!$ids) {
            $visit->agents()->sync([]);

            return;
        }

        if ($visit->exists) {
            $visit->agents()->sync($ids);
        }
    }

    protected function syncExamLines(Visit $visit, array $examItems): void
    {
        if (!$examItems) {
            return;
        }

        $rows = array_map(function ($item) {
            $estConforme = $item['est_conforme'] ?? null;

            if ($estConforme === '' || $estConforme === null) {
                $estConforme = null;
            } else {
                $estConforme = filter_var($estConforme, FILTER_VALIDATE_BOOLEAN);
            }

            return [
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_type' => $item['discount_type'] ?? 'aucun',
                'discount_value' => $item['discount_value'] ?? 0,
                'result' => $item['result'] ?? null,
                'resultat_valeur' => $item['resultat_valeur'] ?? null,
                'unite_mesure' => $item['unite_mesure'] ?? null,
                'valeur_limite' => $item['valeur_limite'] ?? null,
                'est_conforme' => $estConforme,
                'item_type' => 'service',
            ];
        }, array_values($examItems));

        if ($visit->exists) {
            $visit->examLines()->delete();
            foreach ($rows as $row) {
                $visit->examLines()->create($row);
            }
        }
    }

    protected function servicePayload($services): array
    {
        return $services->map(fn ($s) => [
            'id' => (string) $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'price' => (float) $s->price,
            'category_id' => $s->category_id,
            'category_name' => $s->category?->name ?? 'Autres prestations',
            'is_alimentaire' => (bool) $s->is_alimentaire,
        ])->all();
    }
}