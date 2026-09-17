<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\InsuranceContract;
use App\Models\Insurer;
use App\Rules\NiuRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount(['agents', 'invoices', 'insuranceContracts']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('niu', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(15)->appends($request->query());

        return view('clients.index', compact('clients'));
    }

    public function create(Request $request)
    {
        $assureurs = Insurer::orderBy('name')->get();
        $entreprises = Client::where('type', 'entreprise')->orderBy('name')->get();
        $selectedType = in_array($request->query('type'), ['particulier', 'entreprise', 'assureur'], true)
            ? $request->query('type')
            : null;

        return view('clients.create', compact('assureurs', 'entreprises', 'selectedType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:assureur,entreprise,particulier',
            'recipient_type' => 'required|in:individual,business,government,foreign',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_nif' => 'nullable|string|max:50',
            'company_rcs' => 'nullable|string|max:50',
            'niu' => ['nullable', 'string', new NiuRule],
            'rccm' => 'nullable|string|max:50',
            'is_taxable' => 'boolean',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'link_type' => 'nullable|in:assureur,entreprise',
            'assureur_id' => ['nullable', 'required_if:link_type,assureur', Rule::exists('clients', 'id')->where('type', 'assureur')],
            'entreprise_id' => ['nullable', 'required_if:link_type,entreprise', Rule::exists('clients', 'id')->where('type', 'entreprise')],
            'coverage_rate' => 'nullable|required_if:link_type,assureur|numeric|min:0|max:100',
        ]);

        $validated['is_taxable'] = $request->boolean('is_taxable');

        $client = Client::create($validated);

        if ($request->input('link_type') === 'assureur' && $request->filled('assureur_id')) {
            $client->insuranceContracts()->create([
                'insurer_id' => $request->input('assureur_id'),
                'coverage_rate' => $request->input('coverage_rate'),
                'is_active' => true,
            ]);
        } elseif ($request->input('link_type') === 'entreprise' && $request->filled('entreprise_id')) {
            Agent::create([
                'client_id' => $request->input('entreprise_id'),
                'name' => $client->name,
                'phone' => $client->phone,
            ]);
        }

        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $client->load(['agents', 'invoices.client', 'insuranceContracts.insurer']);
        $invoices = $client->invoices()->latest()->paginate(10, ['*'], 'invoices_page');
        $insurers = Insurer::orderBy('name')->get();

        return view('clients.show', compact('client', 'invoices', 'insurers'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'type' => 'required|in:assureur,entreprise,particulier',
            'recipient_type' => 'required|in:individual,business,government,foreign',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_nif' => 'nullable|string|max:50',
            'company_rcs' => 'nullable|string|max:50',
            'niu' => ['nullable', 'string', new NiuRule],
            'rccm' => 'nullable|string|max:50',
            'is_taxable' => 'boolean',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        if ($validated['type'] !== 'entreprise') {
            $validated['company_name'] = null;
            $validated['company_nif'] = null;
            $validated['company_rcs'] = null;
        }

        $validated['is_taxable'] = $request->boolean('is_taxable');

        $client->update($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }

    public function storeAgent(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'matricule' => 'nullable|string|max:50',
        ]);

        $client->agents()->create($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Agent ajouté.');
    }

    public function destroyAgent(Client $client, Agent $agent)
    {
        $agent->delete();

        return redirect()->route('clients.show', $client)->with('success', 'Agent supprimé.');
    }

    public function storeContract(Request $request, Client $client)
    {
        $validated = $request->validate([
            'insurer_id' => ['required', Rule::exists('clients', 'id')->where('type', 'assureur')],
            'coverage_rate' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $client->insuranceContracts()->create($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Contrat d\'assurance ajouté.');
    }

    public function updateContract(Request $request, Client $client, InsuranceContract $contract)
    {
        $validated = $request->validate([
            'insurer_id' => ['required', Rule::exists('clients', 'id')->where('type', 'assureur')],
            'coverage_rate' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $contract->update($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Contrat d\'assurance mis à jour.');
    }

    public function destroyContract(Client $client, InsuranceContract $contract)
    {
        $contract->delete();

        return redirect()->route('clients.show', $client)->with('success', 'Contrat d\'assurance supprimé.');
    }
}
