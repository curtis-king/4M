<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $group = in_array($request->get('group'), ['medical', 'alimentaire'], true)
            ? $request->get('group')
            : null;

        $query = Service::with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($group !== null) {
            $query->whereHas('category', fn ($q) => $q->where('group', $group));
        }

        $services = $query->orderBy('category_id')->orderBy('name')->get();
        $grouped = $services->groupBy('category_id');
        $categories = ServiceCategory::ordered()->whereIn('id', $grouped->keys())->get();
        $allCategories = ServiceCategory::ordered()->get();

        $visits = collect();
        if ($group === 'alimentaire') {
            $visits = \App\Models\Visit::with('client')
                ->whereNotNull('produit_alimentaire')
                ->latest('visit_date')
                ->limit($request->get('limit', 20))
                ->get();
        }

        return view('services.index', compact('services', 'grouped', 'categories', 'allCategories', 'group', 'visits'));
    }

    public function create()
    {
        $categories = ServiceCategory::ordered()->get();

        return view('services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'classification_code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Service créé avec succès.');
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::ordered()->get();

        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'classification_code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service supprimé.');
    }
}
