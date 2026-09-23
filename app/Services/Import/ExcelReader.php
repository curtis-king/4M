<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReader
{
    /**
     * Lit un fichier .xlsx/.xls/.csv et retourne :
     * [
     *   'header' => array<int, string>,   // ligne d'entête brute
     *   'rows'   => array<int, array>,    // lignes de données (indexées par colonne)
     * ]
     */
    public function read(string $path, ?string $sheet = null): array
    {
        if (!file_exists($path)) {
            return ['header' => [], 'rows' => []];
        }

        $spreadsheet = IOFactory::load($path);

        if ($sheet !== null) {
            $index = $spreadsheet->getIndexForSheetName($sheet);
            if ($index === false) {
                return ['header' => [], 'rows' => []];
            }
        } else {
            // Par défaut : première feuille du classeur.
            $index = 0;
        }

        $spreadsheet->setActiveSheetIndex($index);

        $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (empty($data)) {
            return ['header' => [], 'rows' => []];
        }

        $header = array_values(array_map(
            fn ($v) => is_string($v) ? trim($v) : (string) $v,
            array_values($data[array_key_first($data)])
        ));
        unset($data[array_key_first($data)]);

        $rows = [];
        foreach ($data as $row) {
            $values = array_values($row);
            $isEmpty = true;
            foreach ($values as $v) {
                if ($v !== null && trim((string) $v) !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                continue;
            }
            $rows[] = $values;
        }

        return ['header' => $header, 'rows' => $rows];
    }

    public static function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        // Supprime les accents
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n',
            '°' => '', 'œ' => 'oe', 'æ' => 'ae',
        ]);

        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        return $value;
    }
}
