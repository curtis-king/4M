<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevisItem extends Model
{
    protected $fillable = [
        'devis_id',
        'service_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DevisItem $item) {
            $lineTotal = $item->quantity * $item->unit_price;

            $discount = 0;
            if ($item->discount_type === 'pourcentage') {
                $discount = $lineTotal * ($item->discount_value / 100);
            } elseif ($item->discount_type === 'montant') {
                $discount = $item->discount_value;
            }

            $item->discount_amount = $discount;
            $item->net_amount = $lineTotal - $discount;
        });
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}