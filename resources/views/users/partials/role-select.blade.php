@php
    $groupsMap = config('permissions', []);
    $current = old('role', $selected ?? null);
    $roles = $roles ?? \Spatie\Permission\Models\Role::orderBy('label')->orderBy('name')->get();
    $roleSummaries = $roles->mapWithKeys(function ($role) use ($groupsMap) {
        $groups = $role->permissions->pluck('name')
            ->map(fn ($p) => $groupsMap[$p]['group'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->implode(', ');
        return [$role->name => $groups ?: 'Aucun accès'];
    });
@endphp
<select name="role" required
    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    <option value="" disabled {{ $current ? '' : 'selected' }}>Sélectionner un rôle</option>
    @foreach ($roles as $role)
        <option value="{{ $role->name }}" {{ $current === $role->name ? 'selected' : '' }}>
            {{ $role->label ?? ucfirst(str_replace('_', ' ', $role->name)) }}
        </option>
    @endforeach
</select>
<p class="mt-1 text-xs text-gray-400">
    @if ($current && isset($roleSummaries[$current]))
        Accès : {{ $roleSummaries[$current] }}.
    @else
        Choisissez le profil métier de cet utilisateur.
    @endif
</p>