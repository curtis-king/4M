<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\InsuranceContract;
use App\Models\Insurer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsurerController extends Controller
{
    public function index()
    {
        $insurers = Insurer::withCount('contracts')->orderBy('name')->get();

        $insurerRows = $insurers->map(fn ($i) => [
            'id' => $i->id,
            'name' => $i->name,
            'contact_name' => $i->contact_name,
            'phone' => $i->phone,
            'email' => $i->email,
            'contracts_count' => $i->contracts_count,
            'discount_rate' => (float) $i->discount_rate,
            'initials' => collect(explode(' ', $i->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode(''),
            'url' => route('assureurs.assures', $i),
        ])->values();

        return view('insurers.index', compact('insurers', 'insurerRows'));
    }

    public function assures(Insurer $insurer)
    {
        $contracts = $insurer->contracts()->with('client')->latest()->get();

        $candidates = Client::whereIn('type', ['particulier', 'entreprise'])
            ->whereNotIn('id', $contracts->pluck('client_id'))
            ->orderBy('name')
            ->get();

        $statements = $insurer->invoices()
            ->where('is_statement', true)
            ->with('payments')
            ->latest('date')
            ->get();

        $candidateOptions = $candidates->map(fn ($c) => [
            'id' => (string) $c->id,
            'name' => $c->name,
            'display_type' => $c->display_type,
        ])->all();

        return view('insurers.assures', compact('insurer', 'contracts', 'candidates', 'candidateOptions', 'statements'));
    }

    public function updateDiscount(Request $request, Insurer $insurer)
    {
        $validated = $request->validate([
            'discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $rate = (float) ($validated['discount_rate'] ?? 0);

        if ($rate > 0 && $rate < 10) {
            return back()->withErrors(['discount_rate' => 'La remise minimale accordée aux assureurs est de 10 %.'])->withInput();
        }

        $insurer->update(['discount_rate' => $rate > 0 ? $rate : null]);

        return back()->with('success', 'Remise négociée mise à jour pour ' . $insurer->name . '.');
    }

    public function storeAssure(Request $request, Insurer $insurer)
    {
        $validated = $request->validate([
            'mode' => 'required|in:existing,new',
            'client_id' => ['nullable', Rule::exists('clients', 'id')->whereIn('type', ['particulier', 'entreprise'])],
            'new_name' => 'nullable|string|max:255',
            'new_phone' => 'nullable|string|max:20',
            'coverage_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validated['mode'] === 'new') {
            if (empty($validated['new_name'])) {
                return back()->withErrors(['new_name' => 'Le nom du nouvel assuré est requis.'])->withInput();
            }

            $assured = Client::create([
                'type' => 'particulier',
                'recipient_type' => 'individual',
                'name' => $validated['new_name'],
                'phone' => $validated['new_phone'] ?? null,
                'is_taxable' => false,
            ]);
        } else {
            if (empty($validated['client_id'])) {
                return back()->withErrors(['client_id' => 'Choisissez un assuré ou créez-en un nouveau.'])->withInput();
            }

            $assured = Client::findOrFail($validated['client_id']);
        }

        if ($insurer->contracts()->where('client_id', $assured->id)->exists()) {
            return back()->withErrors(['client_id' => 'Cet assuré est déjà relié à cet assureur.'])->withInput();
        }

        $insurer->contracts()->create([
            'client_id' => $assured->id,
            'coverage_rate' => $validated['coverage_rate'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Assuré relié à ' . $insurer->name . '.');
    }

    public function updateAssure(Request $request, Insurer $insurer, InsuranceContract $contract)
    {
        $validated = $request->validate([
            'coverage_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $contract->update([
            'coverage_rate' => $validated['coverage_rate'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Couverture mise à jour.');
    }

    public function destroyAssure(Insurer $insurer, InsuranceContract $contract)
    {
        $contract->delete();

        return back()->with('success', 'Assuré détaché de ' . $insurer->name . '.');
    }
}