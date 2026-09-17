<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'type',
        'recipient_type',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'company_name',
        'company_nif',
        'company_rcs',
        'niu',
        'rccm',
        'is_taxable',
        'contact_name',
        'contact_phone',
        'notes',
        'discount_rate',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'discount_rate' => 'decimal:2',
        ];
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'client_id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(ClientSite::class, 'client_id')->orderBy('sort_order')->orderBy('name');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'client_id');
    }

    public function insuranceContracts(): HasMany
    {
        return $this->hasMany(InsuranceContract::class, 'client_id');
    }

    public function activeContract(): ?InsuranceContract
    {
        return $this->insuranceContracts()
            ->where('is_active', true)
            ->whereNull('end_date')
            ->orWhere('end_date', '>=', now())
            ->latest()
            ->first();
    }

    public function isAssureur(): bool
    {
        return $this->type === 'assureur';
    }

    public function isEntreprise(): bool
    {
        return $this->type === 'entreprise';
    }

    public function getDisplayTypeAttribute(): string
    {
        return match ($this->type) {
            'assureur' => 'Assureur',
            'entreprise' => 'Entreprise',
            default => 'Particulier',
        };
    }
}
