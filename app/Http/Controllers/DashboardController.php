<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Devis;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Reagent;
use App\Models\Visit;

class DashboardController extends Controller
{
    /**
     * Point d'entrée : redirige l'utilisateur vers le tableau de bord
     * de son profil (chaque profil dispose de sa propre page routée).
     */
    public function home()
    {
        $user = request()->user();

        // Chaque utilisateur reçoit une page d'accueil selon son profil.
        // Toute personne connectée a une page : aucun 403 possible au login.
        return match (true) {
            $user->hasRole('administrateur') => $this->administrateur(),
            $user->hasRole('comptable') => $this->comptabilite(),
            default => $this->accueil(),
        };
    }

    // ============================================================
    // Profil « Administrateur / Directeur général »
    // ============================================================
    public function administrateur()
    {
        $ca = Invoice::where('status', '!=', 'annulee')->sum('total');
        $caPaye = Invoice::where('status', 'payee')->sum('total');
        $facturesEnAttente = Invoice::whereIn('status', ['envoyee', 'partiel'])->count();
        $facturesBrouillon = Invoice::where('status', 'brouillon')->count();
        $nbClients = Client::count();
        $nbFacturesMois = Invoice::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', '!=', 'annulee')
            ->count();
        $caMois = Invoice::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', '!=', 'annulee')
            ->sum('total');

        $moisPrecedent = now()->subMonthNoOverflow();
        $caMoisPrecedent = Invoice::whereMonth('date', $moisPrecedent->month)
            ->whereYear('date', $moisPrecedent->year)
            ->where('status', '!=', 'annulee')
            ->sum('total');
        $caEvolutionPct = $caMoisPrecedent > 0
            ? round((($caMois - $caMoisPrecedent) / $caMoisPrecedent) * 100, 1)
            : null;

        $nouveauxClientsMois = Client::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $revenueByMonth = [];
        $examsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $label = ucfirst($month->locale('fr')->isoFormat('MMM'));

            $revenueByMonth[$label] = (float) Invoice::whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', '!=', 'annulee')
                ->sum('total');

            $examsByMonth[$label] = InvoiceItem::where('type', 'analyse')
                ->whereHas('invoice', function ($q) use ($month) {
                    $q->whereMonth('date', $month->month)
                        ->whereYear('date', $month->year)
                        ->where('status', '!=', 'annulee');
                })
                ->count();
        }

        $invoiceStatusStats = $this->invoiceStatusStats();
        $devisStatusStats = $this->devisStatusStats();
        $facturesRecents = Invoice::with([
                'client',
                'items',
                'payments' => fn ($q) => $q->latest('payment_date')->limit(1),
            ])
            ->latest('date')
            ->take(10)
            ->get();
        $stockAlertes = Reagent::whereColumn('quantity', '<=', 'min_quantity')->get();
        $devisRecents = Devis::with('client')->latest('date')->take(5)->get();
        $devisPotentiel = (float) Devis::whereIn('status', ['brouillon', 'envoye', 'accepte'])->sum('total');

        return view('dashboard.administrateur', compact(
            'ca', 'caPaye', 'facturesEnAttente', 'facturesBrouillon',
            'nbClients', 'nbFacturesMois', 'caMois', 'caMoisPrecedent', 'caEvolutionPct',
            'nouveauxClientsMois', 'revenueByMonth', 'examsByMonth', 'invoiceStatusStats',
            'devisStatusStats', 'devisPotentiel', 'devisRecents',
            'facturesRecents', 'stockAlertes'
        ));
    }

    // ============================================================
    // Profil « Comptable »
    // ============================================================
    public function comptabilite()
    {
        $caPaye = (float) Invoice::where('status', 'payee')->sum('total');
        $totalEncaisse = (float) Invoice::sum('paid_amount');
        $totalFacture = (float) Invoice::where('status', '!=', 'annulee')->sum('total');
        $totalRestant = $totalFacture - $totalEncaisse;
        $totalEchu = (float) Invoice::whereIn('status', ['envoyee', 'partiel'])
            ->get()
            ->sum(fn ($i) => max((float) $i->amountDue, 0));

        $caMois = Invoice::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', '!=', 'annulee')
            ->sum('total');
        $moisPrecedent = now()->subMonthNoOverflow();
        $caMoisPrecedent = Invoice::whereMonth('date', $moisPrecedent->month)
            ->whereYear('date', $moisPrecedent->year)
            ->where('status', '!=', 'annulee')
            ->sum('total');
        $caEvolutionPct = $caMoisPrecedent > 0
            ? round((($caMois - $caMoisPrecedent) / $caMoisPrecedent) * 100, 1)
            : null;

        $invoiceStatusStats = $this->invoiceStatusStats();
        $devisStatusStats = $this->devisStatusStats();

        $paymentsRecents = Payment::with(['invoice'])
            ->latest('payment_date')
            ->take(8)
            ->get();

        $nbDevisAConvertir = Devis::where('status', 'accepte')->whereNull('invoice_id')->count();
        $devisAEteindre = Devis::with('client')
            ->where('status', 'accepte')
            ->whereNull('invoice_id')
            ->latest('date')
            ->take(8)
            ->get();

        $facturesRecents = Invoice::with(['client', 'payments' => fn ($q) => $q->latest('payment_date')->limit(1)])
            ->latest('date')
            ->take(8)
            ->get();

        return view('dashboard.comptabilite', compact(
            'caPaye', 'totalEncaisse', 'totalFacture', 'totalRestant', 'totalEchu',
            'caMois', 'caMoisPrecedent', 'caEvolutionPct',
            'invoiceStatusStats', 'devisStatusStats',
            'paymentsRecents', 'nbDevisAConvertir', 'devisAEteindre', 'facturesRecents'
        ));
    }

    // ============================================================
    // Profil « Secrétaire » (aucun montant)
    // ============================================================
    public function accueil()
    {
        $visitesAujourdhui = Visit::with('client')
            ->whereDate('visit_date', now()->toDateString())
            ->orderBy('visit_date')
            ->take(10)
            ->get();

        $nbVisitesJour = $visitesAujourdhui->count();
        $nbVisitesRealisees = $visitesAujourdhui->where('status', 'realisee')->count();
        $nbVisitesPlanifiees = $visitesAujourdhui->where('status', 'planifiee')->count();

        $clientsRecents = Client::latest('created_at')->take(5)->get();
        $facturesDuJour = Invoice::with('client')
            ->whereDate('date', now()->toDateString())
            ->latest('date')
            ->take(6)
            ->get();

        $devisStatusStats = $this->devisStatusStats();
        $devisRecents = Devis::with('client')->latest('date')->take(5)->get();

        $nbClients = Client::count();
        $nbDevisMois = Devis::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $nbVisitesMois = Visit::whereMonth('visit_date', now()->month)->whereYear('visit_date', now()->year)->count();

        return view('dashboard.accueil', compact(
            'visitesAujourdhui', 'nbVisitesJour', 'nbVisitesRealisees', 'nbVisitesPlanifiees',
            'clientsRecents', 'facturesDuJour', 'devisStatusStats', 'devisRecents',
            'nbClients', 'nbDevisMois', 'nbVisitesMois'
        ));
    }

    // ============================================================
    // Helpers partagés
    // ============================================================
    protected function invoiceStatusStats(): array
    {
        return [
            'payee' => [
                'label' => 'Payées',
                'count' => Invoice::where('status', 'payee')->count(),
                'total' => (float) Invoice::where('status', 'payee')->sum('total'),
            ],
            'partiel' => [
                'label' => 'Partielles',
                'count' => Invoice::where('status', 'partiel')->count(),
                'total' => (float) Invoice::where('status', 'partiel')->sum('total'),
            ],
            'envoyee' => [
                'label' => 'Impayées',
                'count' => Invoice::where('status', 'envoyee')->count(),
                'total' => (float) Invoice::where('status', 'envoyee')->sum('total'),
            ],
        ];
    }

    protected function devisStatusStats(): array
    {
        return [
            'brouillon' => ['label' => 'Brouillons', 'count' => Devis::where('status', 'brouillon')->count(), 'total' => (float) Devis::where('status', 'brouillon')->sum('total')],
            'envoye' => ['label' => 'Envoyés', 'count' => Devis::where('status', 'envoye')->count(), 'total' => (float) Devis::where('status', 'envoye')->sum('total')],
            'accepte' => ['label' => 'Acceptés', 'count' => Devis::where('status', 'accepte')->count(), 'total' => (float) Devis::where('status', 'accepte')->sum('total')],
            'converti' => ['label' => 'Convertis', 'count' => Devis::where('status', 'converti')->count(), 'total' => (float) Devis::where('status', 'converti')->sum('total')],
            'refuse' => ['label' => 'Refusés', 'count' => Devis::where('status', 'refuse')->count(), 'total' => (float) Devis::where('status', 'refuse')->sum('total')],
        ];
    }
}