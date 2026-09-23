<?php

namespace App\Services\Import;

use App\Models\Client;

class ClientImporter extends BaseImporter
{
    public function columns(): array
    {
        return [
            'Nom' => 'name',
            'Type' => 'type',
            'Type de destinataire' => 'recipient_type',
            'Email' => 'email',
            'Téléphone' => 'phone',
            'Adresse' => 'address',
            'Ville' => 'city',
            'Nom entreprise' => 'company_name',
            'NIF' => 'nif',
            'NIU' => 'niu',
            'RCCM' => 'rccm',
            'Contact' => 'contact_name',
            'Remise taux (%)' => 'discount_rate',
            'Assujetti à la TVA' => 'is_taxable',
            'Notes' => 'notes',
        ];
    }

    public function exampleRow(): array
    {
        return ['Entreprise ABC', 'entreprise', 'business', 'contact@abc.com', '+242 06 000 00 00', '12 Rue des Frères', 'Brazzaville', 'Entreprise ABC SARLU', '1234567890', 'M123456789012345', 'RC123', 'Jean Dupont', 10, 'Oui', 'Client principal'];
    }

    public function label(): string
    {
        return 'Clients';
    }

    public function description(): string
    {
        return 'Importe une liste de clients (assureurs, entreprises, particuliers). Les doublons (même nom) sont ignorés.';
    }

    protected function import(array $rows): ImportResult
    {
        $result = new ImportResult();

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $result->addError($line, 'Nom du client manquant.');
                continue;
            }

            $normalizedName = mb_strtolower($name);

            $exists = Client::whereRaw('LOWER(name) = ?', [$normalizedName])->exists();
            if ($exists) {
                $result->addWarning($line, "Client « {$name} » déjà existant, ignoré.");
                $result->skipped++;
                continue;
            }

            $type = mb_strtolower(trim((string) ($row['type'] ?? 'particulier')));
            if (! in_array($type, ['assureur', 'entreprise', 'particulier'], true)) {
                $type = match (true) {
                    str_contains($type, 'assur') => 'assureur',
                    str_contains($type, 'entr') => 'entreprise',
                    default => 'particulier',
                };
            }

            $recipientType = mb_strtolower(trim((string) ($row['recipient_type'] ?? 'individual')));
            if (! in_array($recipientType, ['business', 'individual', 'government', 'foreign'], true)) {
                $recipientType = $type === 'entreprise' || $type === 'assureur' ? 'business' : 'individual';
            }

            try {
                $client = Client::create([
                    'type' => $type,
                    'recipient_type' => $recipientType,
                    'name' => $name,
                    'email' => trim((string) ($row['email'] ?? '')) ?: null,
                    'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
                    'address' => trim((string) ($row['address'] ?? '')) ?: null,
                    'city' => trim((string) ($row['city'] ?? '')) ?: null,
                    'company_name' => trim((string) ($row['company_name'] ?? '')) ?: null,
                    'company_nif' => trim((string) ($row['nif'] ?? '')) ?: null,
                    'niu' => trim((string) ($row['niu'] ?? '')) ?: null,
                    'rccm' => trim((string) ($row['rccm'] ?? '')) ?: null,
                    'contact_name' => trim((string) ($row['contact_name'] ?? '')) ?: null,
                    'discount_rate' => FieldHelper::parseNumber($row['discount_rate'] ?? null),
                    'is_taxable' => FieldHelper::toBool($row['is_taxable'] ?? null),
                    'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
                ]);

                $result->imported++;
                $result->created++;
            } catch (\Throwable $e) {
                $result->addError($line, 'Erreur technique : '.$e->getMessage());
            }
        }

        return $result;
    }
}