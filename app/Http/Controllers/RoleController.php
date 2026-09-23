<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $catalogue = $this->catalogue();

        return view('roles.create', compact('catalogue'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        $role = Role::create([
            'name' => $validated['name'],
            'label' => $validated['label'] ?? null,
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Rôle créé avec succès.');
    }

    public function edit(Role $role)
    {
        $catalogue = $this->catalogue();
        $assigned = $role->permissions->pluck('name')->all();

        return view('roles.edit', compact('role', 'catalogue', 'assigned'));
    }

    public function update(Request $request, Role $role)
    {
        $guard = $role->name === 'administrateur';

        $validated = $this->validateRole($request, $role);

        $role->label = $validated['label'] ?? null;

        if (! $guard) {
            $role->name = $validated['name'];
        }

        $role->save();
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour avec succès.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'administrateur') {
            return back()->with('error', 'Le rôle Administrateur ne peut pas être supprimé.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Impossible de supprimer un rôle encore attribué à des utilisateurs.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rôle supprimé avec succès.');
    }

    protected function validateRole(Request $request, ?Role $role = null): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ];

        if ($role?->name === 'administrateur') {
            $rules['name'] = ['nullable'];
        }

        return $request->validate($rules);
    }

    protected function catalogue(): array
    {
        $config = config('permissions', []);

        $grouped = collect(static::allPermissions())
            ->map(fn ($permission) => [
                'name' => $permission,
                'label' => $config[$permission]['label'] ?? ucfirst($permission),
                'group' => $config[$permission]['group'] ?? 'Autres',
            ])
            ->groupBy('group');

        return $grouped->sortKeys()->map->sortBy('label')->toArray();
    }

    public static function allPermissions(): array
    {
        return Permission::orderBy('name')->pluck('name')->all();
    }
}