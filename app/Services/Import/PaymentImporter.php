<?php

namespace App\Services\Import;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class PaymentImporter extends BaseImporter
{
    public function columns(): array
    {
        return [
            'N° facture' => 'invoice_ref',
            'Date' => 'date',
            'Montant' => 'amount',
            'Moyen de paiement' => 'method',
            'Référence' => 'reference',
            'Payeur' => 'payer',
            'Notes' => 'notes',
        ];
    }

    public function exampleRow(): array
    {
        return ['F-4M-2025-000123', '15/01/2025', 25000, 'Virement', 'VIR-2025-001', 'Assurance', 'Paiement partiel'];
    }

    public function label(): string
    {
        return 'Paiements / encaissements';
    }

    public function description(): string
    {
        return 'Importe les règlements reçus (relevés bancaires, mobile money…) et met à jour les factures automatiquement (statut partiel/payée).';
    }

    protected function import(array $rows): ImportResult
    {
        $result = new ImportResult();

        foreach ($rows as $index => $row) {
            $line = $index + 2; // ligne 1 = en-tête

            $invoiceRef = trim((string) ($row['invoice_ref'] ?? ''));
            if ($invoiceRef === '') {
                $result->addError($line, 'N° facture manquant.');
                continue;
            }

            $invoice = Invoice::with('payments')
                ->where('number', $invoiceRef)
                ->orWhere('voucher_number', $invoiceRef)
                ->first();

            if (! $invoice) {
                $result->addError($line, "Facture « {$invoiceRef} » introuvable.");
                continue;
            }

            $amount = FieldHelper::parseNumber($row['amount'] ?? null);
            if ($amount === null || $amount <= 0) {
                $result->addError($line, 'Montant invalide ou nul.');
                continue;
            }

            $date = FieldHelper::parseDate($row['date'] ?? null) ?? $invoice->date;
            $payer = mb_strtolower(trim((string) ($row['payer'] ?? 'patient')));
            if (! in_array($payer, ['assurance', 'patient'], true)) {
                $payer = in_array($payer, ['assureur', 'assurance', 'ass', 'societe'], true) ? 'assurance' : 'patient';
            }

            $method = FieldHelper::methodKey($row['method'] ?? 'especes') ?? 'especes';
            $reference = trim((string) ($row['reference'] ?? '')) ?: null;
            $notes = trim((string) ($row['notes'] ?? '')) ?: null;

            // Vérification du solde restant
            $remaining = $payer === 'assurance'
                ? (float) $invoice->insuranceRemaining
                : (float) $invoice->patientRemaining;

            if ($amount > $remaining + 0.01) {
                $result->addError($line, sprintf(
                    'Montant %s %s supérieur au reste (%s %s).',
                    number_format($amount, 0, ',', ' '),
                    'FCFA',
                    number_format($remaining, 0, ',', ' '),
                    'FCFA'
                ));
                continue;
            }

            try {
                DB::transaction(function () use ($invoice, $amount, $date, $payer, $method, $reference, $notes) {
                    $invoice->payments()->create([
                        'amount' => $amount,
                        'payment_date' => $date,
                        'payer' => $payer,
                        'method' => $method,
                        'reference' => $reference,
                        'notes' => $notes,
                        'invoice_id' => $invoice->id,
                    ]);
                });
                $result->imported++;
                $result->created++;
            } catch (\Throwable $e) {
                $result->addError($line, 'Erreur technique : '.$e->getMessage());
            }
        }

        return $result;
    }
}