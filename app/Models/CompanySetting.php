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
    ];

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'name' => 'Labo 4M',
            'niu' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'nif' => '',
            'rc' => '',
        ]);
    }
}
