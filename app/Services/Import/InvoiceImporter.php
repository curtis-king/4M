<?php

namespace App\Services\Import;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceImporter extends BaseImporter
{
    public function columns(): array
    {
        return [
            'Réf. facture' => 'ref',
            'Client' => 'client',
            'Date' => 'date',
            'Échéance' => 'due_date',
            'Type destinataire' => 'recipient_type',
            'Devise' => 'currency',
            'TVA (%)' => 'tax_rate',
            'Type de remise' => 'discount_type',
            'Valeur de remise' => 'discount_value',
            'Statut' => 'status',
            'Objet' => 'subject',
            'Description ligne' => 'description',
            'Type de ligne' => 'line_type',
            'Quantité' => 'quantity',
            'Prix unitaire' => 'unit_price',
        ];
    }

    public function exampleRow(): array
    {
        return ['INV-1', 'Entreprise ABC', '15/01/2025', '15/02/2025', 'business', 'XAF', 0, 'aucun', 0, 'brouillon', 'Bilan de santé', 'Glycémie', 'analyse', 1, 2500];
    }

    public function label(): string
    {
        return 'Factures';
    }

    public function description(): string
    {
        return 'Crée des factures depuis un fichier Excel. Une LIGNE du fichier = une ligne de facture ; les lignes ayant la même « Réf. facture » sont regroupées dans une même facture. Les totaux (HT, TVA, TTC) sont recalculés automatiquement.';
    }

    protected function import(array $rows): ImportResult
    {
        $result = new ImportResult();

        // Regroupe les lignes par réf
        $groups = [];
        foreach ($rows as $row) {
            $ref = trim((string) ($row['ref'] ?? ''));
            if ($ref === '') {
                continue;
            }
            $groups[$ref][] = $row;
        }

        if (empty($groups)) {
            $result->addError(1, 'Aucune ligne avec une « Réf. facture » trouvée.');
            return $result;
        }

        foreach ($groups as $ref => $groupRows) {
            $first = $groupRows[0];
            $line = array_key_last($groupRows);

            $clientName = trim((string) ($first['client'] ?? ''));
            $clientId = null;
            $walkInName = null;

            if ($clientName === '' || mb_strtolower($clientName) === 'passage') {
                $walkInName = 'Client de passage';
            } else {
                $client = Client::whereRaw('LOWER(name) = ?', [mb_strtolower($clientName)])->first();
                if (! $client) {
                    $result->addError($line ?? 1, "Client « {$clientName} » introuvable (facture {$ref}).");
                    continue;
                }
                $clientId = $client->id;
            }

            $date = FieldHelper::parseDate($first['date'] ?? null);
            if (! $date) {
                $result->addError($line ?? 1, "Date manquante ou invalide (facture {$ref}).");
                continue;
            }

            $dueDate = FieldHelper::parseDate($first['due_date'] ?? null) ?? $date->copy()->addDays(30);

            $status = mb_strtolower(trim((string) ($first['status'] ?? 'brouillon')));
            if (! in_array($status, ['brouillon', 'envoyee', 'payee', 'partiel', 'annulee'], true)) {
                $status = 'brouillon';
            }

            $recipientType = mb_strtolower(trim((string) ($first['recipient_type'] ?? 'individual')));
            if (! in_array($recipientType, ['business', 'individual', 'government', 'foreign'], true)) {
                $recipientType = 'individual';
            }

            $currency = strtoupper(trim((string) ($first['currency'] ?? 'XAF')));
            if (! in_array($currency, ['XAF', 'USD'], true)) {
                $currency = 'XAF';
            }

            $taxRate = FieldHelper::parseNumber($first['tax_rate'] ?? 0) ?? 0;
            $discountType = mb_strtolower(trim((string) ($first['discount_type'] ?? 'aucun')));
            if (! in_array($discountType, ['aucun', 'pourcentage', 'montant'], true)) {
                $discountType = 'aucun';
            }
            $discountValue = FieldHelper::parseNumber($first['discount_value'] ?? 0) ?? 0;

            $subject = trim((string) ($first['subject'] ?? '')) ?: null;

            // Construction des lignes de la facture
            $items = [];
            foreach ($groupRows as $row) {
                $description = trim((string) ($row['description'] ?? ''));
                if ($description === '') {
                    continue;
                }

                $quantity = FieldHelper::parseNumber($row['quantity'] ?? 1) ?? 1;
                $unitPrice = FieldHelper::parseNumber($row['unit_price'] ?? 0) ?? 0;
                $lineType = mb_strtolower(trim((string) ($row['line_type'] ?? 'analyse')));
                if (! in_array($lineType, ['analyse', 'consultation', 'prelevement', 'frais'], true)) {
                    $lineType = 'analyse';
                }

                $items[] = [
                    'description' => $description,
                    'type' => $lineType,
                    'item_type' => 'produit',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_type' => 'aucun',
                    'discount_value' => 0,
                ];
            }

            if (empty($items)) {
                $result->addError($line ?? 1, "Aucune ligne de facture (description manquante) pour la facture {$ref}.");
                continue;
            }

            try {
                DB::transaction(function () use ($clientId, $walkInName, $date, $dueDate, $status, $recipientType, $currency, $taxRate, $discountType, $discountValue, $subject, $items, $ref, $result, $line, $clientName) {
                    $invoice = Invoice::create([
                        'client_id' => $clientId,
                        'walk_in_name' => $walkInName,
                        'date' => $date->toDateString(),
                        'due_date' => $dueDate->toDateString(),
                        'status' => $status,
                        'recipient_type' => $recipientType,
                        'currency' => $currency,
                        'tax_rate' => $taxRate,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'subject' => $subject,
                        'notes' => 'Importée depuis un fichier Excel (réf. '.$ref.').',
                        'created_by' => auth()->id() ?? \App\Models\User::first()?->id,
                    ]);

                    foreach ($items as $item) {
                        $invoice->items()->create($item);
                    }

                    $invoice->recalculate();

                    $result->imported++;
                    $result->created++;
                });
            } catch (\Throwable $e) {
                $result->addError($line ?? 1, "Facture {$ref} : erreur technique : ".$e->getMessage());
            }
        }

        return $result;
    }
}