<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Inactivity timeout in seconds (20 minutes = 1200 seconds).
     */
    protected int $timeout = 1200;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeoutMinutes = Auth::user()->auto_lock_timeout ?? 20;
            $timeoutSeconds = $timeoutMinutes * 60;

            $lastActivity = session('last_activity', time());
            $timeSinceLastActivity = time() - $lastActivity;

            if ($timeSinceLastActivity > $timeoutSeconds) {
                session(['session_locked' => true]);
            }

            if (session('session_locked', false)) {
                $exceptRoutes = ['lock', 'lock.unlock', 'lock.store', 'logout'];

                if (! in_array($request->route()?->getName(), $exceptRoutes)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'locked' => true,
                            'message' => 'Session locked due to inactivity.',
                            'redirect' => route('lock'),
                        ], 423);
                    }

                    return redirect()->route('lock');
                }
            } else {
                session(['last_activity' => time()]);
            }
        }

        return $next($request);
    }
}
