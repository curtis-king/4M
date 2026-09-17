<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Insurer;
use App\Services\StatementService;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    public function __construct(private StatementService $service)
    {
    }

    public function create(Request $request)
    {
        $insurers = Insurer::orderBy('name')->get(['id', 'name', 'discount_rate']);

        $insurerDiscounts = $insurers->pluck('discount_rate', 'id')->map(fn ($d) => (float) $d);

        $selectedInsurer = null;
        if ($request->filled('insurer_id')) {
            $selectedInsurer = Insurer::find($request->integer('insurer_id'));
        }

        return view('invoices.statement-create', compact('insurers', 'selectedInsurer', 'insurerDiscounts'));
    }

    public function options(Request $request)
    {
        $validated = $request->validate([
            'insurer_id' => 'required|exists:clients,id',
            'company_id' => 'nullable|string',
            'month' => 'nullable|date_format:Y-m',
        ]);

        $insurer = Insurer::findOrFail($validated['insurer_id']);

        $companyId = $validated['company_id'] ?? null;
        if ($companyId !== null && $companyId !== 'all') {
            if (!is_numeric($companyId) || !Client::whereKey((int) $companyId)->exists()) {
                return response()->json(['message' => 'Société invalide.'], 422);
            }
            $companyId = (int) $companyId;
        }

        return response()->json(
            $this->service->options($insurer, $companyId, $validated['month'] ?? null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'insurer_id' => 'required|exists:clients,id',
            'month' => 'required|date_format:Y-m',
            'visit_ids' => 'required|array|min:1',
            'visit_ids.*' => 'integer',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $discountValue = (float) $validated['discount_value'];

        if ($discountValue > 0 && $discountValue < 10) {
            return back()->withErrors([
                'discount_value' => 'La remise minimale accordée aux assureurs est de 10 %. Saisissez 0 pour n\'accorder aucune remise.',
            ])->withInput();
        }

        $insurer = Insurer::findOrFail($validated['insurer_id']);

        try {
            $invoice = $this->service->create(
                $insurer,
                $validated['visit_ids'],
                $validated['month'],
                $discountValue
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture de sommation générée avec succès.');
    }
}