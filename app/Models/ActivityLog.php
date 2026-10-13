<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'description', 'ip', 'user_agent'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
