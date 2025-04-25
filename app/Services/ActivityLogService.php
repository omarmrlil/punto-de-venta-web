<?php
namespace App\Services;

use App\Models\Activitylog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public static function log(
    string $action,
    ?string $module = null,
    ?array $data = [],
    ?string $ipAddress = null,
    ?int $user_id = null
): void
{
    Activitylog::create([
        'user_id' => $user_id ?? Auth::user()->id,
        'action' => $action,
        'module' => $module,
        'data' => $data,
        'ip_address' => $ipAddress ?? request()->ip(),
    ]);
}
}
