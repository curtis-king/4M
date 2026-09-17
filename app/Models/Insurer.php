<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insurer extends Client
{
    protected $table = 'clients';
    protected static function booted(): void
    {
        static::addGlobalScope('assureur', function (Builder $builder) {
            $builder->where('type', 'assureur');
        });

        static::saving(function (Insurer $insurer) {
            $insurer->type = 'assureur';
            $insurer->recipient_type = $insurer->recipient_type ?? 'business';
        });
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(InsuranceContract::class, 'insurer_id');
    }

    public function getAssuredCountAttribute(): int
    {
        return (int) ($this->contracts_count ?? 0);
    }
}