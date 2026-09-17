<?php

namespace App\Http\Controllers;

use App\Models\Reagent;
use Illuminate\Http\Request;

class ReagentController extends Controller
{
    public function index(Request $request)
    {
        $query = Reagent::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        }

        $reagents = $query->orderBy('name')->paginate(20);
        $lowStockCount = Reagent::whereColumn('quantity', '<=', 'min_quantity')->count();

        return view('reagents.index', compact('reagents', 'lowStockCount'));
    }

    public function create()
    {
        return view('reagents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'min_quantity' => 'required|numeric|min:0',
            'max_quantity' => 'nullable|numeric|min:0',
        ]);

        Reagent::create($validated);

        return redirect()->route('reagents.index')->with('success', 'Réactif créé avec succès.');
    }

    public function edit(Reagent $reagent)
    {
        return view('reagents.edit', compact('reagent'));
    }

    public function update(Request $request, Reagent $reagent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'min_quantity' => 'required|numeric|min:0',
            'max_quantity' => 'nullable|numeric|min:0',
        ]);

        $reagent->update($validated);

        return redirect()->route('reagents.index')->with('success', 'Réactif mis à jour.');
    }

    public function destroy(Reagent $reagent)
    {
        $reagent->delete();

        return redirect()->route('reagents.index')->with('success', 'Réactif supprimé.');
    }
}
