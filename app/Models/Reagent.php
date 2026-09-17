<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reagent extends Model
{
    protected $fillable = ['name', 'reference', 'unit', 'quantity', 'min_quantity', 'max_quantity'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'max_quantity' => 'decimal:2',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
}
