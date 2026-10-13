<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasUlids;

    protected $fillable = ['client_id', 'insurance_policy_id', 'name', 'path', 'mime', 'size'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }
}
