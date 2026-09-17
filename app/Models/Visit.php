<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $table = 'visites';

    protected $fillable = [
        'client_id',
        'agent_id',
        'beneficiary_name',
        'objet',
        'produit_alimentaire',
        'numero_lot',
        'origine_produit',
        'date_prelevement',
        'type_controle',
        'statut_resultat',
        'insurance_contract_id',
        'visit_date',
        'status',
        'notes',
        'reminder_sent',
        'discount_type',
        'discount_value',
        'discount_amount',
        'subtotal',
        'total',
        'insurance_covered',
        'patient_amount',
        'patient_paid',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'date_prelevement' => 'date',
            'reminder_sent' => 'boolean',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'insurance_covered' => 'decimal:2',
            'patient_amount' => 'decimal:2',
            'patient_paid' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'visit_agents')
            ->withTimestamps()
            ->orderBy('visit_agents.id');
    }

    public function insuranceContract(): BelongsTo
    {
        return $this->belongsTo(InsuranceContract::class);
    }

    public function examLines(): HasMany
    {
        return $this->hasMany(VisitExam::class);
    }

    public function statementItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'source_visit_id');
    }

    public function isBilled(): bool
    {
        return $this->statementItems()->exists();
    }

    public function getIsControleAlimentaireAttribute(): bool
    {
        return !empty($this->produit_alimentaire) || !empty($this->numero_lot)
            || $this->examLines->contains(fn ($line) => $line->isAlimentaire);
    }

    public function getStatutResultatLabelAttribute(): string
    {
        if ($this->statut_resultat === 'conforme') {
            return 'Conforme';
        }

        if ($this->statut_resultat === 'non_conforme') {
            return 'Non conforme';
        }

        return 'En attente';
    }

    public function getAssuredNameAttribute(): string
    {
        $names = $this->agents->pluck('name')->filter();

        if ($names->count() > 1) {
            $shown = $names->take(3)->implode(', ');
            $extra = $names->count() - 3;

            return $extra > 0 ? $shown.'… (+'.$extra.')' : $shown;
        }

        if ($names->count() === 1) {
            return $names->first();
        }

        if ($this->agent) {
            return $this->agent->name;
        }

        if ($this->beneficiary_name) {
            return $this->beneficiary_name;
        }

        return $this->client->name ?? 'Assuré';
    }

    public function getCompanyNameAttribute(): string
    {
        return $this->client->name ?? ($this->agent?->client->name ?? '');
    }

    public function getInvoiceLabelAttribute(): string
    {
        if ($this->objet) {
            return $this->objet;
        }

        if ($this->examLines->count()) {
            return $this->examLines->map(fn ($l) => $l->service?->name)->filter()->implode(', ');
        }

        return 'Consultation';
    }

    public function recalculate(): void
    {
        $subtotal = 0;

        foreach ($this->examLines as $line) {
            $price = $line->unit_price ?? $line->service?->price ?? 0;
            $lineTotal = $line->quantity * $price;

            $discount = 0;
            if ($line->discount_type === 'pourcentage') {
                $discount = $lineTotal * ($line->discount_value / 100);
            } elseif ($line->discount_type === 'montant') {
                $discount = $line->discount_value;
            }

            $discount = min($discount, $lineTotal);

            if ($line->unit_price !== (float) $price
                || $line->discount_amount != $discount
                || $line->net_amount != ($lineTotal - $discount)) {
                $line->update([
                    'unit_price' => $price,
                    'discount_amount' => $discount,
                    'net_amount' => $lineTotal - $discount,
                ]);
            }

            $subtotal += $lineTotal - $discount;
        }

        $subtotal = round($subtotal, 2);

        $discountAmount = 0;
        if ($this->discount_type === 'pourcentage') {
            $discountAmount = $subtotal * ($this->discount_value / 100);
        } elseif ($this->discount_type === 'montant') {
            $discountAmount = $this->discount_value;
        }
        $discountAmount = min($discountAmount, $subtotal);

        $total = round($subtotal - $discountAmount, 2);

        $insuranceCovered = 0;
        $patientAmount = $total;

        if ($this->insurance_contract_id && $this->insuranceContract) {
            $insuranceCovered = round($total * ($this->insuranceContract->coverage_rate / 100), 2);
            $patientAmount = round($total - $insuranceCovered, 2);
        }

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'insurance_covered' => $insuranceCovered,
            'patient_amount' => $patientAmount,
        ]);
    }
}