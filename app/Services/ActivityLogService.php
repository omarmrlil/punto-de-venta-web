<?php
namespace App\Services;

use App\Models\Activitylog;

class ActivityLogService
{
    public static function log(string $action, ?string $module = null, ?array $data = [], ?string $ipAddress = null, ?int $user_id = null,): void
    {
        Activitylog::create([
            'user_id' => $user_id ?? auth()->id,
            'action' => $action,
            'module' => $module,
            'data' => $data,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
