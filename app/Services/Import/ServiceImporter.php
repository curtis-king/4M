<?php

namespace App\Services\Import;

use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceImporter extends BaseImporter
{
    public function columns(): array
    {
        return [
            'Catégorie' => 'category',
            'Nom' => 'name',
            'Code' => 'code',
            'Code classification' => 'classification_code',
            'Prix' => 'price',
            'Description' => 'description',
            'Actif' => 'is_active',
        ];
    }

    public function exampleRow(): array
    {
        return ['Biochimie', 'Glycémie', 'GLY', 'G9.1', 2500, 'Glycémie à jeun', 'Oui'];
    }

    public function label(): string
    {
        return 'Services / grille de prix';
    }

    public function description(): string
    {
        return 'Importe ou met à jour la grille des prestations. Un service avec le même Code est mis à jour ; sinon il est créé.';
    }

    protected function import(array $rows): ImportResult
    {
        $result = new ImportResult();

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $result->addError($line, 'Nom du service manquant.');
                continue;
            }

            $categoryName = trim((string) ($row['category'] ?? ''));
            $categoryId = null;
            if ($categoryName !== '') {
                $category = ServiceCategory::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])->first();
                if (! $category) {
                    $category = ServiceCategory::create(['name' => $categoryName]);
                }
                $categoryId = $category->id;
            }

            $code = trim((string) ($row['code'] ?? '')) ?: null;
            $price = FieldHelper::parseNumber($row['price'] ?? null) ?? 0;
            $isActive = FieldHelper::toBool($row['is_active'] ?? true);

            $attributes = [
                'category_id' => $categoryId,
                'name' => $name,
                'code' => $code,
                'classification_code' => trim((string) ($row['classification_code'] ?? '')) ?: null,
                'price' => $price,
                'description' => trim((string) ($row['description'] ?? '')) ?: null,
                'is_active' => $isActive,
            ];

            try {
                if ($code !== null) {
                    $service = Service::where('code', $code)->first();
                    if ($service) {
                        $service->update($attributes);
                        $result->imported++;
                        $result->updated++;
                        continue;
                    }
                }

                Service::create($attributes);
                $result->imported++;
                $result->created++;
            } catch (\Throwable $e) {
                $result->addError($line, 'Erreur technique : '.$e->getMessage());
            }
        }

        return $result;
    }
}