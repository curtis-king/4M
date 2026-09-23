<?php

namespace App\Services\Import;

use Carbon\Carbon;
use DateTimeInterface;

class FieldHelper
{
    public static function parseNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $v = trim((string) $value);
        $v = str_replace(' ', '', $v);          // "1 000" -> "1000"
        $v = str_replace("\u{00A0}", '', $v);   // espaces insécables
        $v = str_replace(',', '.', $v);         // "1234,56" -> "1234.56"

        $negative = str_starts_with($v, '(') && str_ends_with($v, ')');
        $v = trim($v, '()');

        if (preg_match('/^[-+]?\d*\.?\d+$/', $v) !== 1) {
            return null;
        }

        $n = (float) $v;

        return $negative ? -abs($n) : $n;
    }

    public static function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        $v = trim((string) $value);

        try {
            return Carbon::createFromFormat('d/m/Y', $v);
        } catch (\Throwable) {
            // ignorer
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $v);
        } catch (\Throwable) {
            // ignorer
        }

        try {
            return Carbon::parse($v);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $v = mb_strtolower(trim((string) $value));
        return in_array($v, ['1', 'true', 'vrai', 'oui', 'yes', 'on', 'actif', 'active', 'x'], true);
    }

    public static function methodKey(mixed $value): ?string
    {
        $map = [
            'especes' => 'especes', 'espèces' => 'especes', 'cash' => 'especes', 'liquide' => 'especes',
            'virement' => 'virement', 'bancaire' => 'virement',
            'mobile_money' => 'mobile_money', 'mobile money' => 'mobile_money', 'momo' => 'mobile_money',
            'mtn' => 'mobile_money', 'airtel' => 'mobile_money', 'wave' => 'mobile_money',
            'cheque' => 'cheque', 'chèque' => 'cheque',
            'carte' => 'carte', 'card' => 'carte',
        ];

        $v = mb_strtolower(trim((string) $value));

        if (in_array($v, ['especes', 'virement', 'mobile_money', 'cheque', 'carte'], true)) {
            return $v;
        }

        return $map[$v] ?? null;
    }
}