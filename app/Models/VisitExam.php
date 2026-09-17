<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitExam extends Model
{
    protected $table = 'visit_exams';

    protected $fillable = [
        'visit_id',
        'service_id',
        'result',
        'resultat_valeur',
        'unite_mesure',
        'valeur_limite',
        'est_conforme',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_amount',
        'item_type',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'est_conforme' => 'boolean',
            'unit_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getIsAlimentaireAttribute(): bool
    {
        return (bool) ($this->service?->is_alimentaire ?? false);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->unit_price ?? $this->service?->price ?? 0);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity * $this->effective_price;
    }

    public function getLineNetAttribute(): float
    {
        if ($this->net_amount > 0 || $this->discount_amount > 0) {
            return (float) $this->net_amount;
        }

        $lineTotal = $this->line_total;

        $discount = 0;
        if ($this->discount_type === 'pourcentage') {
            $discount = $lineTotal * ($this->discount_value / 100);
        } elseif ($this->discount_type === 'montant') {
            $discount = $this->discount_value;
        }
        $discount = min($discount, $lineTotal);

        return round($lineTotal - $discount, 2);
    }
}