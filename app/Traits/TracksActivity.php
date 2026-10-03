<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait TracksActivity
{
    public function logActivity($activity, $module, $recordId = null, $oldPayload = null, $newPayload = null)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => $activity,
            'module' => $module,
            'record_id' => $recordId,
            'old_payload' => $oldPayload,
            'new_payload' => $newPayload,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }
}
