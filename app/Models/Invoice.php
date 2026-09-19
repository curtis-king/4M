<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'walk_in_name',
        'agent_id',
        'number',
        'voucher_number',
        'date',
        'due_date',
        'status',
        'insurance_contract_id',
        'pec_number',
        'recipient_type',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total',
        'insurance_covered',
        'patient_amount',
        'paid_amount',
        'notes',
        'created_by',
        'is_statement',
        'invoice_type',
        'subject',
        'sample_nature',
        'company_site',
        'site_id',
        'statement_start_date',
        'statement_end_date',
        'sfec_certified',
        'sfec_certification_number',
        'sfec_signature',
        'sfec_short_signature',
        'sfec_qr_code',
        'sfec_certification_date',
        'sfec_identifier',
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
            'insurance_covered' => 'decimal:2',
            'patient_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'sfec_certified' => 'boolean',
            'sfec_certification_date' => 'datetime',
            'is_statement' => 'boolean',
            'statement_start_date' => 'date',
            'statement_end_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->number)) {
                $year = date('Y');
                $last = static::where('number', 'like', "F-4M-{$year}-%")
                    ->orderByDesc('number')
                    ->first();

                if ($last) {
                    $seq = (int) substr($last->number, -6) + 1;
                } else {
                    $seq = 1;
                }

                $invoice->number = sprintf('F-4M-%s-%06d', $year, $seq);
            }

            if (empty($invoice->voucher_number)) {
                $year = date('Y');
                $last = static::where('voucher_number', 'like', "BON-{$year}-%")
                    ->orderByDesc('voucher_number')
                    ->first();

                if ($last) {
                    $seq = (int) substr($last->voucher_number, -6) + 1;
                } else {
                    $seq = 1;
                }

                $invoice->voucher_number = sprintf('BON-%s-%06d', $year, $seq);
            }

            if (empty($invoice->pec_number) && $invoice->insurance_contract_id) {
                $year = date('Y');
                $last = static::where('pec_number', 'like', "PEC-{$year}-%")
                    ->orderByDesc('pec_number')
                    ->first();

                if ($last) {
                    $seq = (int) substr($last->pec_number, -6) + 1;
                } else {
                    $seq = 1;
                }

                $invoice->pec_number = sprintf('PEC-%s-%06d', $year, $seq);
            }

            if (empty($invoice->created_by)) {
                $invoice->created_by = auth()->id();
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

    public function site(): BelongsTo
    {
        return $this->belongsTo(ClientSite::class);
    }

    public function insuranceContract(): BelongsTo
    {
        return $this->belongsTo(InsuranceContract::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function recalculate(): void
    {
        if ($this->is_statement) {
            $this->recalculateStatement();

            return;
        }

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

        $insuranceCovered = 0;
        $patientAmount = $total;

        if ($this->insurance_contract_id && $this->insuranceContract) {
            $insuranceCovered = $total * ($this->insuranceContract->coverage_rate / 100);
            $patientAmount = $total - $insuranceCovered;
        }

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'insurance_covered' => $insuranceCovered,
            'patient_amount' => $patientAmount,
        ]);
    }

    protected function recalculateStatement(): void
    {
        $fullAmount = round($this->items->sum('net_amount'), 2);
        $base = round($this->items->sum('insurance_part'), 2);

        $discountAmount = 0;
        if ($this->discount_type === 'pourcentage') {
            $discountAmount = $base * ($this->discount_value / 100);
        } elseif ($this->discount_type === 'montant') {
            $discountAmount = min($this->discount_value, $base);
        }

        $total = max(0, round($base - $discountAmount, 2));

        $this->update([
            'subtotal' => $fullAmount,
            'discount_amount' => round($discountAmount, 2),
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => $total,
            'insurance_covered' => $base,
            'patient_amount' => 0,
        ]);
    }

    public function getAmountDueAttribute(): float
    {
        return (float) $this->total - (float) $this->paid_amount;
    }

    public function getInsurancePaidAttribute(): float
    {
        return (float) $this->payments->where('payer', 'assurance')->sum('amount');
    }

    public function getInsuranceRemainingAttribute(): float
    {
        return max(0, (float) $this->insurance_covered - $this->insurancePaid);
    }

    public function getPatientPaidAttribute(): float
    {
        return (float) $this->payments->where('payer', 'patient')->sum('amount');
    }

    public function getPatientRemainingAttribute(): float
    {
        return max(0, (float) $this->patient_amount - $this->patientPaid);
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->invoice_type === 'controle_alimentaire'
            ? 'Contrôle Alimentaire'
            : 'Facture ordinaire';
    }

    public function getIsControleAlimentaireAttribute(): bool
    {
        return $this->invoice_type === 'controle_alimentaire';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'bg-gray-100 text-gray-800',
            'envoyee' => 'bg-blue-100 text-blue-800',
            'payee' => 'bg-green-100 text-green-800',
            'partiel' => 'bg-yellow-100 text-yellow-800',
            'annulee' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
