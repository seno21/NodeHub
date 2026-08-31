<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit log entries.
     */
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('category')) {
            $query->category($request->input('category'));
        }

        if ($request->filled('user_id')) {
            $query->user((int) $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        // Metrics for summary cards
        $retentionDays = (int) config('audit.retention_days', 30);
        $totalLogsCount = AuditLog::count();
        $todayLogsCount = AuditLog::whereDate('created_at', now()->today())->count();
        $rawOldest = AuditLog::min('created_at');
        $oldestLogDate = $rawOldest ? \Illuminate\Support\Carbon::parse($rawOldest) : null;

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $categories = [
            'auth' => 'Autentikasi (Login/Logout)',
            'computer' => 'Perangkat / Device',
            'action' => 'Aksi Remote',
            'vnc' => 'Sesi VNC Connect',
            'tag' => 'Manajemen Tag',
            'profile' => 'Profil Pengguna',
        ];

        return view('audit-logs.index', [
            'logs' => $logs,
            'users' => $users,
            'categories' => $categories,
            'retentionDays' => $retentionDays,
            'totalLogsCount' => $totalLogsCount,
            'todayLogsCount' => $todayLogsCount,
            'oldestLogDate' => $oldestLogDate,
        ]);
    }

    /**
     * Manually trigger pruning of audit logs based on retention days.
     */
    public function prune(Request $request): RedirectResponse
    {
        $retentionDays = (int) config('audit.retention_days', 30);

        $prunedCount = AuditLog::pruneOldLogs($retentionDays);

        return redirect()->route('audit-logs.index')
            ->with('status', __("Audit log (:count baris) yang berusia lebih dari :days hari berhasil dibersihkan!", [
                'count' => $prunedCount,
                'days' => $retentionDays,
            ]));
    }
}
