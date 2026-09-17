<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    protected $fillable = ['name', 'description', 'group', 'sort_order'];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getIsAlimentaireAttribute(): bool
    {
        if ($this->group !== null) {
            return $this->group === 'alimentaire';
        }

        return str_contains($this->name, 'Alimentaire') || $this->name === 'Nutrition et Composition';
    }
}
