<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class AuditLogger
{
    public static function record(
        string $module,
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),

                'module' => $module,
                'action' => $action,

                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id' => $model?->getKey(),

                'description' => $description,

                'old_values' => self::cleanValues($oldValues),
                'new_values' => self::cleanValues($newValues),

                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'method' => request()?->method(),
                'url' => request()?->fullUrl(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private static function cleanValues(?array $values): ?array
    {
        if (! $values) {
            return null;
        }

        $hiddenKeys = [
            'password',
            'password_confirmation',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($hiddenKeys as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[hidden]';
            }
        }

        return $values;
    }
}