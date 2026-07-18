<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ActivityLog;

class AuditController extends Controller
{
    public function index()
    {
        $logs = AuditLog::where('tenant_id', tenant_id())
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('panels.audit.index', compact('logs'));
    }

    public function activity()
    {
        $activities = ActivityLog::where('tenant_id', tenant_id())
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('panels.audit.activity', compact('activities'));
    }
}
