<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Rules\NiuRule;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $settings = CompanySetting::instance();

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'niu' => ['required', 'string', new NiuRule],
            'logo' => 'nullable|string|max:500',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'nif' => 'required|string|max:50',
            'rc' => 'required|string|max:50',
            'patente' => 'nullable|string|max:50',
            'cnss' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_rib' => 'nullable|string|max:100',
            'sfec_api_key' => 'nullable|string|max:500',
            'sfec_api_key_sandbox' => 'nullable|string|max:500',
            'sfec_environment' => 'required|in:production,sandbox',
        ]);

        $settings = CompanySetting::instance();
        $settings->update($validated);

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }
}
