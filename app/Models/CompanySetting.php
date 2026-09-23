<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'niu',
        'logo',
        'address',
        'phone',
        'email',
        'nif',
        'rc',
        'patente',
        'cnss',
        'bank_name',
        'bank_rib',
        'sfec_api_key',
        'sfec_api_key_sandbox',
        'sfec_environment',
        'accounting_clients',
        'accounting_ventes',
        'accounting_tva',
        'accounting_caisse',
        'accounting_banque',
        'accounting_assurance',
    ];

    public function setNiuAttribute($value): void
    {
        $this->attributes['niu'] = $value === null ? null : strtoupper(trim((string) $value));
    }

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'name' => "CENTRE 4M DE SANTE DU DIAGNOSTIC ET DE L'EXPERTISE",
            'niu' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'nif' => '',
            'rc' => '',
        ]);
    }
}
