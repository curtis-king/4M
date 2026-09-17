<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Devis;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevisController extends Controller
{
    public function index(Request $request)
    {
        $query = Devis::with(['client', 'agent']);

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
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $devis = $query->latest('date')->paginate(15);

        return view('devis.index', compact('devis'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::with('category')->where('is_active', true)->orderBy(ServiceCategory::select('sort_order')->whereColumn('service_categories.id', 'services.category_id'))->orderBy('name')->get();
        $serviceOptions = $this->servicePayload($services);

        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::with('agents')->find($request->client_id);
        }

        return view('devis.create', compact('clients', 'services', 'serviceOptions', 'selectedClient'));
    }

    public function store(Request $request)
    {
        if ($request->has('items') && is_string($request->input('items'))) {
            $request->merge(['items' => json_decode($request->input('items'), true) ?? []]);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'walk_in_name' => 'required_without:client_id|nullable|string|max:255',
            'agent_id' => 'nullable|exists:agents,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
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
        } else {
            $validated['walk_in_name'] = null;
        }

        $validated['created_by'] = auth()->id();

        $devis = Devis::create($validated);

        foreach ($items as $item) {
            $item['service_id'] = $item['service_id'] ?: null;
            $devis->items()->create($item);
        }

        $devis->recalculate();

        return redirect()->route('devis.show', $devis)->with('success', 'Devis créé avec succès.');
    }

    public function show(Devis $devis)
    {
        $devis->load(['client', 'agent', 'items.service', 'creator', 'invoice']);

        return view('devis.show', compact('devis'));
    }

    public function edit(Devis $devis)
    {
        if ($devis->status !== 'brouillon') {
            return redirect()->route('devis.show', $devis)->with('error', 'Seuls les devis en brouillon peuvent être modifiés.');
        }

        $clients = Client::orderBy('name')->get();
        $services = Service::with('category')->where('is_active', true)->orderBy(ServiceCategory::select('sort_order')->whereColumn('service_categories.id', 'services.category_id'))->orderBy('name')->get();
        $serviceOptions = $this->servicePayload($services);
        $selectedClient = $devis->client?->load('agents');

        return view('devis.edit', compact('devis', 'clients', 'services', 'serviceOptions', 'selectedClient'));
    }

    public function update(Request $request, Devis $devis)
    {
        if ($devis->status !== 'brouillon') {
            return redirect()->route('devis.show', $devis)->with('error', 'Seuls les devis en brouillon peuvent être modifiés.');
        }

        if ($request->has('items') && is_string($request->input('items'))) {
            $request->merge(['items' => json_decode($request->input('items'), true) ?? []]);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'walk_in_name' => 'required_without:client_id|nullable|string|max:255',
            'agent_id' => 'nullable|exists:agents,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
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
        } else {
            $validated['walk_in_name'] = null;
        }

        $devis->update($validated);

        $devis->items()->delete();
        foreach ($items as $item) {
            $item['service_id'] = $item['service_id'] ?: null;
            $devis->items()->create($item);
        }

        $devis->recalculate();

        return redirect()->route('devis.show', $devis)->with('success', 'Devis mis à jour.');
    }

    public function destroy(Devis $devis)
    {
        if ($devis->status !== 'brouillon') {
            return redirect()->route('devis.index')->with('error', 'Devis non supprimable.');
        }

        $devis->delete();

        return redirect()->route('devis.index')->with('success', 'Devis supprimé.');
    }

    public function updateStatus(Request $request, Devis $devis)
    {
        $request->validate([
            'status' => 'required|in:brouillon,envoye,accepte,refuse,converti',
        ]);

        $devis->update(['status' => $request->status]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function print(Devis $devis)
    {
        $devis->load(['client', 'agent', 'items.service', 'creator']);
        $company = CompanySetting::instance();

        return view('devis.print', compact('devis', 'company'));
    }

    public function convert(Devis $devis)
    {
        if ($devis->status === 'converti' && $devis->invoice_id) {
            return back()->with('error', 'Ce devis a déjà été converti en facture.');
        }

        if (!in_array($devis->status, ['envoye', 'accepte'])) {
            return back()->with('error', 'Seuls les devis envoyés ou acceptés peuvent être convertis en facture.');
        }

        $devis->load('items');

        $invoice = DB::transaction(function () use ($devis) {
            $invoice = Invoice::create([
                'client_id' => $devis->client_id,
                'walk_in_name' => $devis->walk_in_name,
                'agent_id' => $devis->agent_id,
                'date' => $devis->date,
                'due_date' => $devis->due_date,
                'status' => 'brouillon',
                'currency' => $devis->currency,
                'subtotal' => $devis->subtotal,
                'tax_rate' => $devis->tax_rate,
                'tax_amount' => $devis->tax_amount,
                'discount_type' => $devis->discount_type,
                'discount_value' => $devis->discount_value,
                'discount_amount' => $devis->discount_amount,
                'total' => $devis->total,
                'notes' => $devis->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($devis->items as $item) {
                $invoice->items()->create([
                    'service_id' => $item->service_id,
                    'description' => $item->description,
                    'type' => $item->type,
                    'item_type' => $item->service_id ? 'service' : 'produit',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_type' => $item->discount_type,
                    'discount_value' => $item->discount_value,
                ]);
            }

            $invoice->recalculate();
            $invoice->refresh();

            $devis->update([
                'status' => 'converti',
                'invoice_id' => $invoice->id,
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', "Devis {$devis->number} converti en facture {$invoice->number}.");
    }

    public function apiClientData(Request $request, Client $client)
    {
        $client->load('agents');

        return response()->json(['client' => $client]);
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