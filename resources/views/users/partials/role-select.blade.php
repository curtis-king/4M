@php
    $roleDescriptions = [
        'directeur' => 'Accès total : gestion, facturation, visites, utilisateurs, paramètres.',
        'partenaire' => 'Lecture seule : tableau de bord, clients, factures, visites.',
        'comptable' => 'Facturation, paiements et contrats d\'assurance.',
        'agent_labo' => 'Visites, résultats d\'examens et stock de réactifs.',
        'receptionniste' => 'Accueil, clients et prise de rendez-vous.',
    ];
    $current = old('role', $selected ?? null);
@endphp
<select name="role" required
    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    <option value="" disabled {{ $current ? '' : 'selected' }}>Sélectionner un rôle</option>
    @foreach ($roles as $role)
        <option value="{{ $role->name }}" {{ $current === $role->name ? 'selected' : '' }}>
            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
        </option>
    @endforeach
</select>
<p class="mt-1 text-xs text-gray-400">{{ $roleDescriptions[$current] ?? 'Choisissez le rôle métier de cet utilisateur.' }}</p>
