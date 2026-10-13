<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'full_name', 'nic', 'date_of_birth', 'gender', 'marital_status', 'dependents', 'phone', 'whatsapp',
        'email', 'address', 'city', 'occupation', 'monthly_income', 'stage', 'source', 'tags', 'notes', 'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'nic' => 'encrypted',           // NIC numbers are stored encrypted with the app key
            'date_of_birth' => 'date',
            'monthly_income' => 'decimal:2',
            'tags' => 'array',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', trim($this->full_name))[0] ?: $this->full_name;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /** NIC shown as 1990•••••45V so it isn't exposed on screen by default */
    public function getMaskedNicAttribute(): ?string
    {
        $nic = (string) $this->nic;
        if ($nic === '') {
            return null;
        }

        return mb_strlen($nic) <= 5 ? $nic : mb_substr($nic, 0, 4).str_repeat('•', max(1, mb_strlen($nic) - 7)).mb_substr($nic, -3);
    }

    /** Days until the next birthday (0 = today), null without a birth date */
    public function daysToBirthday(?Carbon $today = null): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }
        $today = ($today ?? now())->copy()->startOfDay();
        $next = $this->date_of_birth->copy()->year($today->year);
        if ($this->date_of_birth->format('m-d') === '02-29' && ! $today->isLeapYear()) {
            $next = Carbon::create($today->year, 2, 28);
        }
        if ($next->lt($today)) {
            $next->addYear();
        }

        return (int) $today->diffInDays($next);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $q->where(fn ($w) => $w->where('full_name', 'like', $like)
            ->orWhere('phone', 'like', $like)
            ->orWhere('whatsapp', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('city', 'like', $like));
    }
}
