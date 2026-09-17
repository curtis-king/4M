<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceContract extends Model
{
    protected $fillable = [
        'client_id',
        'insurer_id',
        'contract_number',
        'coverage_rate',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'coverage_rate' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InsuranceContract $contract) {
            if (empty($contract->start_date)) {
                $contract->start_date = now()->toDateString();
            }

            if (empty($contract->contract_number)) {
                $year = date('Y');
                $last = static::where('contract_number', 'like', "CTR-{$year}-%")
                    ->orderByDesc('contract_number')
                    ->first();

                if ($last) {
                    $seq = (int) substr($last->contract_number, -4) + 1;
                } else {
                    $seq = 1;
                }

                $contract->contract_number = sprintf('CTR-%s-%04d', $year, $seq);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class, 'insurer_id');
    }
}
