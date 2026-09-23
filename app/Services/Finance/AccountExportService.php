<?php

namespace App\Services\Finance;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountExportService
{
    protected array $accounts;

    public function __construct()
    {
        $s = CompanySetting::instance();
        $this->accounts = [
            'clients' => $s->accounting_clients ?: '411',
            'ventes' => $s->accounting_ventes ?: '701',
            'tva' => $s->accounting_tva ?: '44571',
            'caisse' => $s->accounting_caisse ?: '571',
            'banque' => $s->accounting_banque ?: '512',
            'assurance' => $s->accounting_assurance ?: '411',
        ];
    }

    /**
     * Génère un CSV pour les écritures de VENTES (Journal Ventes).
     * Format Sage : Journal;Date;Numéro de pièce;Compte;Libellé;Débit;Crédit;Devise
     */
    public function exportSales(string $from, string $to, array $filters = []): string
    {
        $query = Invoice::with(['client', 'insuranceContract.insurer', 'payments'])
            ->whereBetween('date', [$from, $to])
            ->whereNotIn('status', ['annulee']);

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['certified'])) {
            $query->where('sfec_certified', true);
        }
        if (!empty($filters['statement'])) {
            $query->where('is_statement', true);
        }
        if (!empty($filters['controle_alimentaire'])) {
            $query->where('invoice_type', 'controle_alimentaire');
        }

        $invoices = $query->orderBy('date')->orderBy('number')->get();

        $lines = [];
        $lines[] = $this->header();

        foreach ($invoices as $inv) {
            $date = Carbon::parse($inv->date)->format('d/m/Y');
            $piece = $inv->number;
            $devise = $inv->currency ?? 'XAF';
            $clientName = $inv->recipient_name;
            $objet = $inv->subject ?: ($inv->notes ? str($inv->notes)->limit(60) : 'Facturation');
            $baseLibelle = sprintf('%s - %s', $piece, $clientName);
            if ($objet && $objet !== 'Facturation') {
                $baseLibelle .= ' - ' . str($objet)->limit(40);
            }

            $totalTTC = (float) $inv->total;
            $taxe = (float) $inv->tax_amount;
            $ht = (float) $inv->subtotal - (float) $inv->discount_amount;
            if ($ht < 0) $ht = 0;
            $htEffectif = round($totalTTC - $taxe, 2);

            // Cas 1 : Facture avec assurance (couverture partielle) ou sommation
            $hasInsurance = $inv->insurance_contract_id && $inv->insurance_covered > 0;

            if ($hasInsurance) {
                $insurance = round((float) $inv->insurance_covered, 2);
                $patient = round((float) $inv->patient_amount, 2);

                // Débit part patient (411 Client)
                if ($patient > 0) {
                    $libPatient = $baseLibelle . ' (part patient)';
                    $lines[] = $this->row('VT', $date, $piece, $this->accounts['clients'], $libPatient, $patient, 0, $devise);
                }

                // Débit part assureur (411 Assurance)
                if ($insurance > 0) {
                    $assureur = $inv->insuranceContract?->insurer?->name ?? 'Assurance';
                    $libAss = $baseLibelle . sprintf(' (part assureur - %s)', str($assureur)->limit(30));
                    $lines[] = $this->row('VT', $date, $piece, $this->accounts['assurance'], $libAss, $insurance, 0, $devise);
                }

                // Crédit Ventes (HT effectif approx.) — ventiler HT/TVA ci-dessous
                $htAss = $htEffectif;
                if ($htAss > 0) {
                    $lines[] = $this->row('VT', $date, $piece, $this->accounts['ventes'], $baseLibelle . ' (ventes)', 0, $htAss, $devise);
                }
                if ($taxe > 0) {
                    $lines[] = $this->row('VT', $date, $piece, $this->accounts['tva'], $baseLibelle . ' (TVA)', 0, $taxe, $devise);
                }
                continue;
            }

            // Cas 2 : Client classique (sans assurance)
            if ($totalTTC > 0) {
                // Débit 411 Clients
                $lines[] = $this->row('VT', $date, $piece, $this->accounts['clients'], $baseLibelle, $totalTTC, 0, $devise);
            }
            if ($htEffectif > 0) {
                $lines[] = $this->row('VT', $date, $piece, $this->accounts['ventes'], $baseLibelle . ' (ventes)', 0, $htEffectif, $devise);
            }
            if ($taxe > 0) {
                $lines[] = $this->row('VT', $date, $piece, $this->accounts['tva'], $baseLibelle . ' (TVA)', 0, $taxe, $devise);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Génère un CSV pour les ENCISSEMENTS (Journal Banque/Caisse).
     */
    public function exportReceipts(string $from, string $to, array $filters = []): string
    {
        $query = Payment::with(['invoice.client', 'invoice'])
            ->whereBetween('payment_date', [$from, $to]);

        $payments = $query->orderBy('payment_date')->orderBy('id')->get();

        $lines = [];
        $lines[] = $this->header();

        foreach ($payments as $p) {
            $date = Carbon::parse($p->payment_date)->format('d/m/Y');
            $inv = $p->invoice;
            $piece = $inv?->number ?? ('P-' . $p->id);
            $devise = 'XAF';
            $client = $inv?->recipient_name ?? 'Règlement';
            $ref = $p->reference ? ' - Réf. ' . $p->reference : '';
            $libelle = sprintf('%s - %s%s (paiement %s)', $piece, $client, $ref, $p->payer ?? 'général');

            $montant = (float) $p->amount;
            if ($montant <= 0) continue;

            $compteTresor = match ($p->method) {
                'especes', 'mobile_money' => $this->accounts['caisse'],
                'virement', 'cheque', 'carte' => $this->accounts['banque'],
                default => $this->accounts['banque'],
            };

            // Débit Trésorerie (512/571), Crédit 411 Clients
            $lines[] = $this->row('BQ', $date, $piece, $compteTresor, $libelle, $montant, 0, $devise);
            $lines[] = $this->row('BQ', $date, $piece, $this->accounts['clients'], $libelle . ' (règlement client)', 0, $montant, $devise);
        }

        return implode(PHP_EOL, $lines);
    }

    protected function header(): string
    {
        return 'Journal;Date;Numéro de pièce;Compte;Libellé;Débit;Crédit;Devise';
    }

    protected function row(string $journal, string $date, string $piece, string $compte, string $libelle, float $debit, float $credit, string $devise): string
    {
        $fmt = fn(float $v) => number_format($v, 2, ',', '');
        $lib = str_replace([';', "\n", "\r"], ' ', $libelle);
        $piece = str_replace([';', "\n", "\r"], ' ', $piece);

        return sprintf('%s;%s;%s;%s;%s;%s;%s;%s',
            $journal,
            $date,
            $piece,
            $compte,
            $lib,
            $fmt($debit),
            $fmt($credit),
            $devise
        );
    }

    public function filenameSales(string $from, string $to): string
    {
        return sprintf('export_sage_ventes_%s_%s.csv', Carbon::parse($from)->format('Ymd'), Carbon::parse($to)->format('Ymd'));
    }

    public function filenameReceipts(string $from, string $to): string
    {
        return sprintf('export_sage_encaissements_%s_%s.csv', Carbon::parse($from)->format('Ymd'), Carbon::parse($to)->format('Ymd'));
    }
}