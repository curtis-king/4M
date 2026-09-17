<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'source_visit_id',
        'service_id',
        'description',
        'type',
        'item_type',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_amount',
        'classification_code',
        'company_name',
        'assured_name',
        'coverage_rate',
        'insurance_part',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'coverage_rate' => 'decimal:2',
            'insurance_part' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item) {
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function sourceVisit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'source_visit_id');
    }
}
