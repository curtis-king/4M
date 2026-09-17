<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'method',
        'reference',
        'payer',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->sum('amount');
            $status = $totalPaid >= $invoice->total ? 'payee' : 'partiel';
            $invoice->update(['paid_amount' => $totalPaid, 'status' => $status]);
        });

        static::deleted(function (Payment $payment) {
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->sum('amount');
            $status = $totalPaid >= $invoice->total ? 'payee' : ($totalPaid > 0 ? 'partiel' : $invoice->status);
            if ($invoice->status !== 'annulee') {
                $invoice->update(['paid_amount' => $totalPaid, 'status' => $status]);
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
