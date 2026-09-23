<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['clients' => [], 'invoices' => []]);
        }

        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(5)
            ->get();

        $invoices = Invoice::with('client')
            ->where('number', 'like', "%{$q}%")
            ->orWhere('voucher_number', 'like', "%{$q}%")
            ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$q}%"))
            ->latest('date')
            ->limit(5)
            ->get();

        $canViewFinancial = auth()->user()->can('view financial data');

        return response()->json([
            'clients' => $clients->map(fn (Client $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'type' => $c->display_type,
                'url' => route('clients.show', $c),
            ]),
            'invoices' => $invoices->map(fn (Invoice $i) => [
                'id' => $i->id,
                'number' => $i->number,
                'client' => $i->recipient_name,
                'total' => $canViewFinancial ? number_format($i->total, 0, ',', ' ') : null,
                'status' => ucfirst($i->status),
                'url' => route('invoices.show', $i),
            ]),
        ]);
    }
}
