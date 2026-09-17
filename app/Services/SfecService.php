<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Invoice;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SfecService
{
    protected const PAYMENT_METHOD_MAP = [
        'especes' => 'cash',
        'virement' => 'bank_transfer',
        'mobile_money' => 'mobile_money',
        'cheque' => 'cheque',
        'carte' => 'card',
    ];

    protected const DISCOUNT_TYPE_MAP = [
        'aucun' => 'fixed',
        'montant' => 'fixed',
        'pourcentage' => 'percentage',
    ];

    protected const ITEM_TYPE_MAP = [
        'produit' => 'product',
        'service' => 'service',
    ];

    /**
     * Certify an invoice with SFEC. Returns the decoded response data on success.
     *
     * @throws SfecException
     */
    public function certify(Invoice $invoice): array
    {
        $company = CompanySetting::instance();

        $environment = $company->sfec_environment ?: 'sandbox';
        $apiKey = $environment === 'production' ? $company->sfec_api_key : $company->sfec_api_key_sandbox;

        if (empty($apiKey)) {
            throw new SfecException("Aucune clé API SFEC ({$environment}) configurée dans Paramètres.");
        }

        $baseUrl = $environment === 'production'
            ? 'https://api.sfec.gouv.cg'
            : 'https://sandbox.api.sfec.gouv.cg';

        $payload = $this->buildPayload($invoice, $company);

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(20)->post("{$baseUrl}/api/v1/invoices", $payload);
        } catch (ConnectionException $e) {
            throw new SfecException("Impossible de contacter le serveur SFEC ({$environment}) : ".$e->getMessage());
        }

        if ($response->failed()) {
            throw new SfecException($this->formatError($response->status(), $response->json()));
        }

        return $response->json();
    }

    protected function buildPayload(Invoice $invoice, CompanySetting $company): array
    {
        $rate = (float) $invoice->tax_rate;

        $subtotal = 0.0;
        $totalLineDiscount = 0.0;
        $taxT = 0.0;
        $taxR = 0.0;
        $exempt = 0.0;

        $formattedItems = [];
        foreach ($invoice->items as $item) {
            $quantity = (float) $item->quantity;
            $unitPrice = (float) $item->unit_price;
            $lineSubtotal = round($quantity * $unitPrice, 2);
            $lineDiscount = round((float) $item->discount_amount, 2);
            $net = round($lineSubtotal - $lineDiscount, 2);

            $itemTax = 0.0;
            if ($rate === 5.0) {
                $itemTax = round($net * 0.05, 2);
                $taxR += $itemTax;
            } elseif ($rate === 18.0) {
                $itemTax = round($net * 0.18, 2);
                $taxT += $itemTax;
            } else {
                $exempt += $net;
            }

            $formattedItems[] = [
                'designation' => (string) $item->description,
                'type' => self::ITEM_TYPE_MAP[$item->item_type] ?? 'service',
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $lineSubtotal,
                'discount_amount' => $lineDiscount,
                'discount_type' => self::DISCOUNT_TYPE_MAP[$item->discount_type] ?? 'fixed',
                'net_amount' => $net,
                'tax_rate' => (string) (int) $rate,
                'tax_amount' => $itemTax,
                'total_amount' => round($net + $itemTax, 2),
                'classification_code' => $this->cleanString($item->classification_code),
            ];

            $subtotal += $net;
            $totalLineDiscount += $lineDiscount;
        }

        $subtotal = round($subtotal, 2);
        $totalLineDiscount = round($totalLineDiscount, 2);
        $taxAmount = round($taxT + $taxR, 2);
        $discountAmount = round((float) $invoice->discount_amount, 2);
        $totalAmount = round($subtotal + $taxAmount - $discountAmount, 2);
        $amountDue = round(max(0, $totalAmount - (float) $invoice->paid_amount), 2);

        $client = $invoice->client;
        $lastPayment = $invoice->payments->sortByDesc('payment_date')->first();

        $recipientNiu = $client ? $this->normalizeNiu($client->niu) : null;

        if ($invoice->recipient_type === 'business' && !$recipientNiu) {
            $who = $client?->name ?? 'le destinataire';
            throw new SfecException(
                "NIU manquant pour la certification : la fiche de « {$who} » (destinataire professionnel) ne contient aucun NIU valide (16 ou 17 chiffres). "
                .'Renseignez-le depuis la fiche client puis réessayez.'
            );
        }

        return [
            'invoice_id' => $invoice->number,
            'invoice_type' => 'salesInvoice',
            'taxpayer_niu' => $this->cleanString($company->niu),
            'recipient_type' => $invoice->recipient_type,
            'recipient_name' => $client->name ?? $this->cleanString($invoice->walk_in_name) ?? 'Client de passage',
            'recipient_niu' => $recipientNiu,
            'recipient_rccm' => $client ? $this->cleanString($client->rccm) : null,
            'recipient_address' => $client ? $this->cleanString($client->address) : null,
            'recipient_phone' => $client ? $this->normalizePhone($client->phone) : null,
            'recipient_email' => $client ? $this->cleanString($client->email) : null,
            'is_recipient_taxable' => (bool) ($client->is_taxable ?? false),
            'invoice_due_date' => optional($invoice->due_date)->toIso8601String(),
            'notes' => $this->cleanString($invoice->notes),
            'subtotal' => $subtotal,
            'total_tax_t_amount' => $taxT,
            'total_tax_r_amount' => $taxR,
            'total_exempt_amount' => $exempt,
            'total_tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_line_discount_amount' => $totalLineDiscount,
            'additional_cent_tax' => 0,
            'electronic_stamp_duty' => 0,
            'total_amount' => $totalAmount,
            'amount_due' => $amountDue,
            'currency' => $invoice->currency ?: 'XAF',
            'payment_method' => self::PAYMENT_METHOD_MAP[$lastPayment->method ?? 'especes'] ?? 'cash',
            'payment_reference' => $lastPayment->reference ?? null,
            'payment_date' => $lastPayment ? optional($lastPayment->payment_date)->toIso8601String() : null,
            'items' => $formattedItems,
        ];
    }

    protected function cleanString($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeNiu($value): ?string
    {
        $niu = $this->cleanString($value);

        if (!$niu) {
            return null;
        }

        $length = strlen($niu);

        return ($length === 16 || $length === 17) ? $niu : null;
    }

    protected function normalizePhone($value): ?string
    {
        $phone = $this->cleanString($value);

        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        // National format: 06/05/04 XXXXXXX → +2420XXXXXXXX
        if (preg_match('/^0([456]\d{7})$/', $digits)) {
            return '+242'.$digits;
        }

        // Local with 242 prefix: 242069990011 → +242069990011
        if (preg_match('/^242(0[456]\d{7})$/', $digits, $m)) {
            return '+242'.$m[1];
        }

        return $phone;
    }

    protected function formatError(int $status, ?array $body): string
    {
        $prefix = match ($status) {
            400 => 'Données invalides',
            401 => 'Clé API SFEC invalide ou inactive',
            409 => 'Facture déjà certifiée côté SFEC',
            422 => 'Erreur de validation métier',
            default => "Erreur SFEC ({$status})",
        };

        $messages = [];
        foreach (['error', 'errors', 'fail', 'message', 'detail', 'details', 'validation'] as $key) {
            $value = $body[$key] ?? null;
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $field => $errs) {
                    if (is_string($field) && !is_int($field)) {
                        $messages[] = $field.': '.(is_array($errs) ? implode('; ', $errs) : $errs);
                    } elseif (is_array($errs) && isset($errs['msg'])) {
                        $messages[] = $errs['msg'];
                    } else {
                        $messages[] = is_array($errs) ? implode('; ', $errs) : (string) $errs;
                    }
                }
            } else {
                $messages[] = (string) $value;
            }
        }

        $messages = array_values(array_unique(array_filter($messages)));

        return $messages ? "{$prefix} : ".implode(' | ', $messages) : $prefix;
    }
}