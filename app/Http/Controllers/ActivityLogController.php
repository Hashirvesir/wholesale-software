<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by Module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $users = User::all();
        $modules = ActivityLog::select('module')->distinct()->pluck('module');

        return view('activity_logs.index', compact('logs', 'users', 'modules'));
    }

    public function show(ActivityLog $activityLog)
    {
        return view('activity_logs.show', compact('activityLog'));
    }
}
