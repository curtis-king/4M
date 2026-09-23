<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\SfecException;
use App\Services\SfecService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'agent', 'insuranceContract.insurer']);

        if ($request->boolean('certified')) {
            $query->where('sfec_certified', true);
        }

        if ($request->boolean('statement')) {
            $query->where('is_statement', true);
        }

        if ($request->boolean('controle_alimentaire')) {
            $query->where('invoice_type', 'controle_alimentaire');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('voucher_number', 'like', "%{$search}%")
                  ->orWhere('pec_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $statsQuery = clone $query;
        $totalFacture = (clone $statsQuery)->sum('total');
        $totalEncaisse = (clone $statsQuery)->sum('paid_amount');
        $nbFacturesFiltrees = (clone $statsQuery)->count();
        $totalRestant = $totalFacture - $totalEncaisse;

        $invoices = $query->latest('date')->paginate(15)->appends($request->query());

        return view('invoices.index', compact('invoices', 'totalFacture', 'totalEncaisse', 'totalRestant', 'nbFacturesFiltrees'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::with('category')->where('is_active', true)->orderBy(ServiceCategory::select('sort_order')->whereColumn('service_categories.id', 'services.category_id'))->orderBy('name')->get();
        $serviceOptions = $this->servicePayload($services);

        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::with(['insuranceContracts.insurer', 'agents'])->find($request->client_id);
        }

        return view('invoices.create', compact('clients', 'services', 'serviceOptions', 'selectedClient'));
    }

    public function store(Request $request)
    {
        if ($request->has('items') && is_string($request->input('items'))) {
            $request->merge(['items' => json_decode($request->input('items'), true) ?? []]);
        }

        $request->merge(['invoice_type' => $request->input('invoice_type', 'standard')]);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'walk_in_name' => 'required_without:client_id|nullable|string|max:255',
            'agent_id' => 'nullable|exists:agents,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'insurance_contract_id' => 'nullable|exists:insurance_contracts,id',
            'recipient_type' => 'required|in:business,individual,government,foreign',
            'invoice_type' => 'required|in:standard,controle_alimentaire',
            'subject' => 'nullable|string|max:255',
            'sample_nature' => 'nullable|string|max:255',
            'company_site' => 'nullable|string|max:255',
            'currency' => 'required|in:XAF,USD',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount_type' => 'required|in:aucun,pourcentage,montant',
            'discount_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required|string',
            'items.*.type' => 'required|in:analyse,consultation,prelevement,frais',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:aucun,pourcentage,montant',
            'items.*.discount_value' => 'required|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        if (empty($validated['client_id'])) {
            $validated['client_id'] = null;
            $validated['agent_id'] = null;
            $validated['insurance_contract_id'] = null;
        } else {
            $validated['walk_in_name'] = null;
        }

        $validated['created_by'] = auth()->id();

        $invoice = Invoice::create($validated);

        foreach ($items as $item) {
            $item['item_type'] = $item['service_id'] ? 'service' : 'produit';
            $item['service_id'] = $item['service_id'] ?: null;
            $invoice->items()->create($item);
        }

        $invoice->recalculate();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Facture créée avec succès.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'agent', 'insuranceContract.insurer', 'items.service', 'items.sourceVisit.examLines.service', 'payments', 'creator']);
        $payments = $invoice->payments()->latest('payment_date')->get();

        return view('invoices.show', compact('invoice', 'payments'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'brouillon') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Seules les factures en brouillon peuvent être modifiées.');
        }

        if ($invoice->is_statement) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Les factures de sommation ne sont pas modifiables manuellement. Supprimez-la puis régénérez si nécessaire.');
        }

        $clients = Client::orderBy('name')->get();
        $services = Service::with('category')->where('is_active', true)->orderBy(ServiceCategory::select('sort_order')->whereColumn('service_categories.id', 'services.category_id'))->orderBy('name')->get();
        $serviceOptions = $this->servicePayload($services);
        $selectedClient = $invoice->client?->load(['insuranceContracts.insurer', 'agents', 'sites']);

        return view('invoices.edit', compact('invoice', 'clients', 'services', 'serviceOptions', 'selectedClient'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'brouillon') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Seules les factures en brouillon peuvent être modifiées.');
        }

        if ($invoice->is_statement) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Les factures de sommation ne sont pas modifiables manuellement.');
        }

        if ($request->has('items') && is_string($request->input('items'))) {
            $request->merge(['items' => json_decode($request->input('items'), true) ?? []]);
        }

        $request->merge(['invoice_type' => $request->input('invoice_type', 'standard')]);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'walk_in_name' => 'required_without:client_id|nullable|string|max:255',
            'agent_id' => 'nullable|exists:agents,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'insurance_contract_id' => 'nullable|exists:insurance_contracts,id',
            'recipient_type' => 'required|in:business,individual,government,foreign',
            'invoice_type' => 'required|in:standard,controle_alimentaire',
            'subject' => 'nullable|string|max:255',
            'sample_nature' => 'nullable|string|max:255',
            'company_site' => 'nullable|string|max:255',
            'currency' => 'required|in:XAF,USD',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount_type' => 'required|in:aucun,pourcentage,montant',
            'discount_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required|string',
            'items.*.type' => 'required|in:analyse,consultation,prelevement,frais',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:aucun,pourcentage,montant',
            'items.*.discount_value' => 'required|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        if (empty($validated['client_id'])) {
            $validated['client_id'] = null;
            $validated['agent_id'] = null;
            $validated['insurance_contract_id'] = null;
        } else {
            $validated['walk_in_name'] = null;
        }

        $invoice->update($validated);

        $invoice->items()->delete();
        foreach ($items as $item) {
            $item['item_type'] = $item['service_id'] ? 'service' : 'produit';
            $item['service_id'] = $item['service_id'] ?: null;
            $invoice->items()->create($item);
        }

        $invoice->recalculate();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['brouillon', 'annulee'])) {
            return redirect()->route('invoices.index')->with('error', 'Facture non supprimable.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Facture supprimée.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:brouillon,envoyee,payee,partiel,annulee',
        ]);

        $invoice->update(['status' => $request->status]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['client', 'agent', 'insuranceContract.insurer', 'items.service', 'items.sourceVisit.examLines.service', 'payments', 'creator']);
        $company = CompanySetting::instance();

        return view('invoices.print', compact('invoice', 'company'));
    }

    public function certify(Request $request, Invoice $invoice, SfecService $sfec)
    {
        if ($invoice->sfec_certified) {
            return back()->with('error', 'Facture déjà certifiée SFEC.');
        }

        if ($invoice->status === 'brouillon') {
            return back()->with('error', 'Envoyez d\'abord la facture avant de la certifier.');
        }

        if ($invoice->payments()->count() === 0) {
            return back()->with('error', 'Enregistrez au moins un paiement avant de certifier la facture.');
        }

        $invoice->load(['client', 'items', 'payments']);

        try {
            $data = $sfec->certify($invoice);
        } catch (SfecException $e) {
            return back()->with('error', $e->getMessage());
        }

        $qrCode = $data['qr_code'] ?? null;
        if ($qrCode && str_starts_with($qrCode, 'data:')) {
            $qrCode = substr($qrCode, strpos($qrCode, ',') + 1);
        }

        $invoice->update([
            'sfec_certified' => true,
            'sfec_certification_number' => $data['certification_number'] ?? null,
            'sfec_certification_date' => $data['certification_date'] ?? now(),
            'sfec_signature' => $data['signature'] ?? null,
            'sfec_short_signature' => $data['short_signature'] ?? null,
            'sfec_qr_code' => $qrCode,
            'sfec_identifier' => $data['identifier'] ?? null,
        ]);

        return back()->with('success', 'Facture certifiée SFEC avec succès.');
    }

    public function apiClientData(Request $request, Client $client)
    {
        $client->load(['insuranceContracts.insurer', 'agents', 'sites']);

        return response()->json([
            'client' => $client,
            'active_contract' => $client->activeContract(),
        ]);
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
