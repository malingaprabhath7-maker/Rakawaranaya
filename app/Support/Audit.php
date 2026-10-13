<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class Audit
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, ?int $userId = null): void
    {
        $request = request();
        ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey() !== null ? (string) $subject->getKey() : null,
            'description' => $description ? Str::limit($description, 250) : null,
            'ip' => $request?->ip(),
            'user_agent' => $request ? Str::limit((string) $request->userAgent(), 250) : null,
        ]);
    }
}
