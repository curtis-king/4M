<?php

namespace App\Http\Controllers;

use App\Services\Finance\AccountExportService;
use App\Services\Import\ImportManager;
use App\Services\Import\TemplateGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    public function exports()
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->endOfMonth()->format('Y-m-d');
        $clients = \App\Models\Client::orderBy('name')->get(['id', 'name']);

        return view('finance.exports', compact('defaultFrom', 'defaultTo', 'clients'));
    }

    public function exportSales(Request $request, AccountExportService $service)
    {
        $from = $request->filled('date_from') ? $request->date_from : now()->startOfMonth()->toDateString();
        $to = $request->filled('date_to') ? $request->date_to : now()->endOfMonth()->toDateString();

        $csv = $service->exportSales($from, $to, $this->filters($request));

        return $this->csvResponse($csv, $service->filenameSales($from, $to));
    }

    public function exportReceipts(Request $request, AccountExportService $service)
    {
        $from = $request->filled('date_from') ? $request->date_from : now()->startOfMonth()->toDateString();
        $to = $request->filled('date_to') ? $request->date_to : now()->endOfMonth()->toDateString();

        $csv = $service->exportReceipts($from, $to, $this->filters($request));

        return $this->csvResponse($csv, $service->filenameReceipts($from, $to));
    }

    public function imports()
    {
        $labels = ImportManager::labels();
        $descriptions = ImportManager::descriptions();

        return view('finance.imports', compact('labels', 'descriptions'));
    }

    public function importTemplate(string $type)
    {
        try {
            $importer = ImportManager::make($type);
        } catch (\InvalidArgumentException $e) {
            abort(404);
        }

        $path = TemplateGenerator::generate($importer);
        $filename = 'modele_import_'.$type.'.xlsx';

        return response()->download(
            $path,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function importStore(Request $request, string $type)
    {
        if (! array_key_exists($type, ImportManager::TYPES)) {
            abort(404);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return back()->with('error', 'Format de fichier non supporté. Utilisez un fichier .xlsx, .xls ou .csv.');
        }

        try {
            $stored = $request->file('file')->store('imports', 'local');
            $path = storage_path('app/private/'.$stored);
        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de lire le fichier : '.$e->getMessage());
        }

        $importer = ImportManager::make($type);

        try {
            $reader = new \App\Services\Import\ExcelReader();
            $data = $reader->read($path);
        } catch (\Throwable $e) {
            return back()->with('error', 'Fichier illisible ou corrompu : '.$e->getMessage());
        }

        if (empty($data['header']) || empty($data['rows'])) {
            return back()->with('error', 'Le fichier est vide ou ne contient pas de données.');
        }

        $result = $importer->importFile($data['header'], $data['rows']);

        return view('finance.import-report', [
            'type' => $type,
            'importer' => $importer,
            'result' => $result,
        ]);
    }

    protected function filters(Request $request): array
    {
        return $request->only(['client_id', 'status', 'certified', 'statement', 'controle_alimentaire']);
    }

    protected function csvResponse(string $csv, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $bom = "\xEF\xBB\xBF"; // UTF-8 BOM pour Excel/Sage

        return response()->streamDownload(function () use ($csv) {
            echo $bom.$csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}