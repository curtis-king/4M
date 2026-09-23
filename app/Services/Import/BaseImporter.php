<?php

namespace App\Services\Import;

abstract class BaseImporter
{
    /**
     * Liste des colonnes : label (en-tête du modèle) => clé (assoc).
     */
    abstract public function columns(): array;

    /**
     * Une ligne d'exemple (valeurs ordonnées comme columns()).
     */
    abstract public function exampleRow(): array;

    abstract public function label(): string;

    abstract public function description(): string;

    /**
     * Reçoit les lignes brutes (array de tableaux indexés) + la ligne d'en-tête,
     * les mappe sur les colonnes attendues puis exécute l'import.
     */
    public function importFile(array $header, array $rows): ImportResult
    {
        $mapped = $this->mapRows($header, $rows);

        return $this->import($mapped);
    }

    /**
     * Import des lignes mappées (clé => valeur).
     */
    abstract protected function import(array $rows): ImportResult;

    protected function mapRows(array $header, array $rows): array
    {
        $map = [];
        foreach ($this->columns() as $label => $key) {
            $map[ExcelReader::normalizeHeader($label)] = $key;
        }

        $mapped = [];
        foreach ($rows as $row) {
            $assoc = [];
            foreach ($header as $colIndex => $colName) {
                $key = $map[ExcelReader::normalizeHeader((string) $colName)] ?? null;
                if (! $key) {
                    continue;
                }
                $assoc[$key] = $row[$colIndex] ?? null;
            }
            $mapped[] = $assoc;
        }

        return $mapped;
    }

    protected function val(array $row, string $key): mixed
    {
        return $row[$key] ?? null;
    }
}