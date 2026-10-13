<?php

namespace App\Models;

use App\Support\Options;
use Database\Factories\InsurancePolicyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class InsurancePolicy extends Model
{
    /** @use HasFactory<InsurancePolicyFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'client_id', 'insurer', 'type', 'policy_number', 'plan_name', 'sum_assured', 'premium', 'frequency',
        'start_date', 'next_due_date', 'end_date', 'status', 'commission_rate', 'vehicle_number', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sum_assured' => 'decimal:2',
            'premium' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'start_date' => 'date',
            'next_due_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_on');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function periodsPerYear(): int
    {
        $m = Options::FREQUENCY_MONTHS[$this->frequency] ?? 0;

        return $m > 0 ? intdiv(12, $m) : 1;
    }

    /** Annualised premium equivalent (single premium counts one tenth, the industry convention) */
    public function annualPremium(): float
    {
        $p = (float) $this->premium;

        return $this->frequency === 'single' ? round($p / 10, 2) : round($p * $this->periodsPerYear(), 2);
    }

    /** The due date after $from for this policy's frequency (null for single premium) */
    public function dueAfter(CarbonInterface $from): ?CarbonInterface
    {
        $months = Options::FREQUENCY_MONTHS[$this->frequency] ?? 0;
        if ($months === 0) {
            return null;
        }
        // keep the original day of month (31st → end of shorter months, then back to 31st)
        $day = $this->start_date?->day ?? $from->day;
        $next = $from->copy()->startOfMonth()->addMonthsNoOverflow($months);

        return $next->day(min($day, $next->daysInMonth));
    }

    public function isOverdue(?CarbonInterface $today = null): bool
    {
        return $this->status === 'active' && $this->next_due_date && $this->next_due_date->lt(($today ?? now())->startOfDay());
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function getLabelAttribute(): string
    {
        return trim(($this->insurer ?? '').' · '.($this->plan_name ?: __(Options::POLICY_TYPES[$this->type] ?? $this->type)), ' ·');
    }
}
