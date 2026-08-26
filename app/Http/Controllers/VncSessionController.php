<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Services\VncSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VncSessionController extends Controller
{
    public function __construct(private VncSessionService $sessions) {}

    /**
     * Start a new VNC session and redirect to the viewer.
     */
    public function start(Request $request, Computer $computer): RedirectResponse|JsonResponse
    {
        $bridgeMessage = __(
            'The remote gateway (websockify) is not running. Start it on the server with: php artisan vnc:bridge --daemon',
        );

        if (config('vnc.bridge_check') && ! $this->sessions->isBridgeUp()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $bridgeMessage], 503);
            }

            return back()->withErrors(['connect' => $bridgeMessage]);
        }

        $unreachableMessage = __(
            '":name" is unreachable on :ip::port — remote session was not started.',
            ['name' => $computer->name, 'ip' => $computer->ip_address, 'port' => $computer->vnc_port],
        );

        if (! $this->sessions->isReachable($computer)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $unreachableMessage], 503);
            }

            return back()->withErrors(['connect' => $unreachableMessage]);
        }

        $token = $this->sessions->createSession($computer);

        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => route('viewer.show', ['token' => $token]),
            ]);
        }

        return redirect()->route('viewer.show', ['token' => $token]);
    }

    /**
     * Display the noVNC viewer page.
     */
    public function show(string $token): View
    {
        return view('viewer', ['token' => $token]);
    }

    /**
     * Return the one-time connection ticket for a session token.
     */
    public function ticket(Request $request, string $token): JsonResponse
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{40}$/', $token) === 1, 404);

        $session = $this->sessions->getSession($token);
        abort_if($session === null, 404);

        return response()->json([
            'ws_url' => $this->websockifyUrl($request).'/websockify?token='.$token,
            'password' => $session['vnc_password'],
            'os_type' => $session['os_type'],
            'device_name' => $session['name'] ?? '',
        ]);
    }

    /**
     * Resolve the public websockify base URL.
     *
     * Falls back to the request host (including port) so the portal works
     * seamlessly behind a reverse proxy routing /websockify.
     */
    private function websockifyUrl(Request $request): string
    {
        if ($url = config('vnc.websockify.ws_url')) {
            return rtrim($url, '/');
        }

        $scheme = $request->isSecure() ? 'wss' : 'ws';

        return "{$scheme}://{$request->getHttpHost()}";
    }
}
