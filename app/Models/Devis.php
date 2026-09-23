<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devis extends Model
{
    protected $fillable = [
        'client_id',
        'walk_in_name',
        'agent_id',
        'number',
        'date',
        'due_date',
        'status',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total',
        'notes',
        'created_by',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Devis $devis) {
            if (empty($devis->number)) {
                $year = date('Y');
                $last = static::where('number', 'like', "D-4M-{$year}-%")
                    ->orderByDesc('number')
                    ->first();

                if ($last) {
                    $seq = (int) substr($last->number, -6) + 1;
                } else {
                    $seq = 1;
                }

                $devis->number = sprintf('D-4M-%s-%06d', $year, $seq);
            }

            if (empty($devis->created_by)) {
                $devis->created_by = auth()->id();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getRecipientNameAttribute(): string
    {
        return $this->client->name ?? $this->walk_in_name ?? 'Client de passage';
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DevisItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items->sum('net_amount');

        $discountAmount = 0;
        if ($this->discount_type === 'pourcentage') {
            $discountAmount = $subtotal * ($this->discount_value / 100);
        } elseif ($this->discount_type === 'montant') {
            $discountAmount = $this->discount_value;
        }

        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $afterDiscount * ($this->tax_rate / 100);
        $total = $afterDiscount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'bg-gray-100 text-gray-800',
            'envoye' => 'bg-blue-100 text-blue-800',
            'accepte' => 'bg-green-100 text-green-800',
            'refuse' => 'bg-red-100 text-red-800',
            'converti' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusDotAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'bg-gray-400',
            'envoye' => 'bg-blue-500',
            'accepte' => 'bg-green-500',
            'refuse' => 'bg-red-500',
            'converti' => 'bg-purple-500',
            default => 'bg-gray-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'Brouillon',
            'envoye' => 'Envoyé',
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            'converti' => 'Converti en facture',
            default => ucfirst($this->status),
        };
    }

    public function getWorkflowStepsAttribute(): array
    {
        $steps = [
            ['key' => 'brouillon', 'label' => 'Brouillon'],
            ['key' => 'envoye', 'label' => 'Envoyé'],
            ['key' => 'accepte', 'label' => 'Accepté'],
            ['key' => 'converti', 'label' => 'Converti'],
        ];

        $current = $this->status === 'refuse' ? 'refuse' : $this->status;
        $reach = array_search($current, array_column($steps, 'key'), true) ?: -1;

        foreach ($steps as $i => $step) {
            $steps[$i]['state'] = $i < $reach ? 'done' : ($i === $reach ? 'current' : 'pending');
        }

        return $steps;
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['refuse']);
    }

    public function scopeExpired($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['converti', 'refuse']);
    }
}