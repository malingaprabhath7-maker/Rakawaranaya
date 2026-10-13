<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['insurance_policy_id', 'paid_on', 'amount', 'commission', 'method', 'reference', 'covers_due_date', 'notes'];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'covers_due_date' => 'date',
            'amount' => 'decimal:2',
            'commission' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id')->withTrashed();
    }
}
