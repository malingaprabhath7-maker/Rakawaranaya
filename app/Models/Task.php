<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasUlids;

    protected $fillable = ['client_id', 'title', 'type', 'due_on', 'due_time', 'done_at', 'notes'];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'done_at' => 'datetime',
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
}
