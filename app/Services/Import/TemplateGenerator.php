<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TemplateGenerator
{
    public static function generate(BaseImporter $importer): string
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Données');

        $columns = $importer->columns();
        $headers = array_keys($columns);
        $example = $importer->exampleRow();

        // En-têtes
        foreach ($headers as $colIndex => $header) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $cell = $sheet->getCell($column.'1');
            $cell->setValue($header);
            $sheet->getStyle($cell->getCoordinate())
                ->getFont()->setBold(true);
            $sheet->getStyle($cell->getCoordinate())
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F0FE');
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Ligne d'exemple
        foreach ($example as $colIndex => $value) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $cell = $sheet->getCell($column.'2');
            $cell->setValueExplicit($value, is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $sheet->getStyle('A2:'.$sheet->getHighestColumn().'2')
            ->getFont()->getColor()->setARGB('FF6B7280');

        // Feuille d'aide
        $help = $spreadsheet->createSheet();
        $help->setTitle('Aide');
        $help->setCellValue('A1', 'Comment remplir ce modèle ?');
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $help->setCellValue('A3', $importer->description());
        $help->setCellValue('A5', 'Remplissez le fichier à partir de la ligne 2 (la première ligne contient les intitulés de colonnes).');
        $help->setCellValue('A6', 'Supprimez la ligne d\'exemple avant de téléverser votre fichier.');
        $help->setCellValue('A7', 'Vous pouvez réordonner les colonnes comme vous le souhaitez : le système reconnaît chaque colonne par son intitulé.');
        $help->setCellValue('A8', 'Formats attendus : dates JJ/MM/AAAA, montants numériques (ex. 25000 ou 25 000,50).');

        $writer = new Xlsx($spreadsheet);
        $path = tempnam(sys_get_temp_dir(), 'modele_').'.xlsx';
        $spreadsheet->setActiveSheetIndex(0);
        $writer->save($path);

        return $path;
    }
}