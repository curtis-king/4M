<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice.client', 'invoice.insuranceContract.insurer'])
            ->whereHas('invoice');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('invoice', function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('voucher_number', 'like', "%{$search}%")
                  ->orWhere('pec_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('payer')) {
            $query->where('payer', $request->payer);
        }

        if ($request->filled('client_id')) {
            $query->whereHas('invoice', fn ($q) => $q->where('client_id', $request->client_id));
        }

        if ($request->filled('status')) {
            $query->whereHas('invoice', fn ($q) => $q->where('status', $request->status));
        }

        $totalCollected = (float) $query->clone()->sum('amount');
        $nbPayments = $query->clone()->count();

        $byPayer = $query->clone()
            ->selectRaw('payer, SUM(amount) as total')
            ->groupBy('payer')
            ->pluck('total', 'payer')
            ->map(fn ($v) => (float) $v);

        $byMethod = $query->clone()
            ->selectRaw('method, COUNT(*) as nb, SUM(amount) as total')
            ->groupBy('method')
            ->orderBy('total', 'desc')
            ->get()
            ->keyBy('method')
            ->map(fn ($row) => ['nb' => $row->nb, 'total' => (float) $row->total])
            ->all();

        $monthly = $query->clone()
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyData = $monthly->mapWithKeys(function ($row) {
            $label = Carbon::createFromFormat('Y-m', $row->month)->locale('fr')->isoFormat('MMM YYYY');

            return [$label => (float) $row->total];
        });

        $clients = Client::orderBy('name')->get(['id', 'name']);

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();

        // Factures non soldées (impayés), y compris sans aucun paiement
        $unpaidQuery = Invoice::query()
            ->with(['client', 'insuranceContract.insurer'])
            ->where('status', '!=', 'annulee')
            ->whereRaw('COALESCE(paid_amount, 0) < total');

        if ($request->filled('search')) {
            $search = $request->search;
            $unpaidQuery->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('voucher_number', 'like', "%{$search}%")
                  ->orWhere('pec_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('client_id')) {
            $unpaidQuery->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $unpaidQuery->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $unpaidQuery->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $unpaidQuery->where('due_date', '<=', $request->date_to);
        }

        $totalDue = (float) $unpaidQuery->clone()
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as due')
            ->value('due');

        $nbUnpaid = $unpaidQuery->clone()->count();

        $overdueTotal = (float) $unpaidQuery->clone()
            ->where('due_date', '<', now()->toDateString())
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as due')
            ->value('due');

        $byClient = $unpaidQuery->clone()
            ->selectRaw('client_id, SUM(total - COALESCE(paid_amount, 0)) as due')
            ->groupBy('client_id')
            ->orderByDesc('due')
            ->get()
            ->map(fn ($row) => ['client' => $row->client?->name ?? 'Client', 'due' => (float) $row->due]);

        $unpaidInvoices = $unpaidQuery
            ->orderByRaw('(total - COALESCE(paid_amount, 0)) desc')
            ->orderBy('due_date')
            ->paginate(20, ['*'], 'impayes_page')
            ->withQueryString();

        return view('payments.index', compact(
            'payments', 'totalCollected', 'nbPayments', 'byPayer', 'byMethod', 'monthlyData', 'clients',
            'unpaidInvoices', 'totalDue', 'nbUnpaid', 'overdueTotal', 'byClient'
        ));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $amount = str_replace([' ', "\u{00A0}"], '', trim((string) $request->input('amount')));
        if (str_contains($amount, ',')) {
            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '.', $amount);
        }
        $request->merge(['amount' => $amount]);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|in:especes,virement,mobile_money,cheque,carte',
            'payer' => 'required|in:assurance,patient',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $invoice->load('payments');
        $remaining = $validated['payer'] === 'assurance' ? $invoice->insuranceRemaining : $invoice->patientRemaining;

        if ($validated['amount'] > $remaining + 0.01) {
            $label = $validated['payer'] === 'assurance' ? "la part assurance" : "la part patient (ticket modérateur)";

            return back()->withErrors([
                'amount' => "Ce montant dépasse {$label} restant à payer sur cette facture (".number_format($remaining, 0, ',', ' ')." FCFA restants).",
            ])->withInput();
        }

        $invoice->payments()->create($validated);

        return back()->with('success', 'Paiement enregistré.');
    }

    public function destroy(Invoice $invoice, Payment $payment)
    {
        $payment->delete();

        return back()->with('success', 'Paiement supprimé.');
    }
}
