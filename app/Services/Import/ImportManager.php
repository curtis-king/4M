<?php

namespace App\Services\Import;

class ImportManager
{
    public const TYPES = [
        'payments' => PaymentImporter::class,
        'clients' => ClientImporter::class,
        'services' => ServiceImporter::class,
        'invoices' => InvoiceImporter::class,
    ];

    public static function make(string $type): BaseImporter
    {
        $class = self::TYPES[$type] ?? null;
        if (! $class) {
            throw new \InvalidArgumentException("Type d'import inconnu : {$type}");
        }

        return new $class();
    }

    public static function labels(): array
    {
        $labels = [];
        foreach (self::TYPES as $key => $class) {
            $labels[$key] = (new $class())->label();
        }

        return $labels;
    }

    public static function descriptions(): array
    {
        $descriptions = [];
        foreach (self::TYPES as $key => $class) {
            $descriptions[$key] = (new $class())->description();
        }

        return $descriptions;
    }
}