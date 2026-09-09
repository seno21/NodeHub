<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Services\AuditLogger;
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

        $vncSocket = @fsockopen($computer->ip_address, (int) $computer->vnc_port, $vncErrno, $vncErrstr, 2.0);
        if (!is_resource($vncSocket)) {
            $errLower = strtolower($vncErrstr ?: '');
            if ($vncErrno === 111 || str_contains($errLower, 'refused')) {
                $unreachableMessage = "VNC PORT CLOSED: VNC service on \"{$computer->name}\" ({$computer->ip_address}:{$computer->vnc_port}) is refused. Ensure VNC server is running on target.";
            } elseif ($vncErrno === 110 || str_contains($errLower, 'timed out')) {
                $unreachableMessage = "CONNECTION TIMEOUT: \"{$computer->name}\" ({$computer->ip_address}:{$computer->vnc_port}) did not respond. Check network or IP address.";
            } else {
                $unreachableMessage = "\"{$computer->name}\" is unreachable on {$computer->ip_address}:{$computer->vnc_port} — " . ($vncErrstr ?: 'Failed to open remote session.');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $unreachableMessage], 503);
            }

            return back()->withErrors(['connect' => $unreachableMessage]);
        } else {
            fclose($vncSocket);
        }

        $token = $this->sessions->createSession($computer);

        AuditLogger::log('vnc.connect', "Opened VNC Remote Desktop session to {$computer->name} ({$computer->ip_address}:{$computer->vnc_port})", [
            'computer_id' => $computer->id,
            'computer_name' => $computer->name,
            'ip_address' => $computer->ip_address,
            'vnc_port' => $computer->vnc_port,
            'session_token' => substr($token, 0, 10) . '...',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => route('viewer.show', ['token' => $token]),
            ]);
        }

        return redirect()->route('viewer.show', ['token' => $token]);
    }

    /**
     * Start a direct VNC session without needing a pre-registered computer (Fast Connect).
     */
    public function fastConnect(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'string', 'max:255'],
            'vnc_port' => ['nullable', 'integer', 'between:1,65535'],
            'vnc_password' => ['nullable', 'string', 'max:255'],
            'os_type' => ['nullable', 'string', 'in:windows,linux,mac'],
            'save_device' => ['nullable', 'boolean'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $ipAddress = trim($validated['ip_address']);
        $vncPort = (int) ($validated['vnc_port'] ?? 5900);
        $vncPassword = $validated['vnc_password'] ?? null;
        $osType = $validated['os_type'] ?? 'linux';
        $saveDevice = (bool) ($validated['save_device'] ?? false);
        $deviceName = !empty($validated['device_name']) ? trim($validated['device_name']) : "Fast Connect ({$ipAddress})";

        if ($saveDevice && Computer::query()->where('ip_address', $ipAddress)->exists()) {
            $errorMessage = __('IP Address is already registered. Uncheck "Save to Device List" for Fast Connect only.');
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $errorMessage,
                    'errors' => [
                        'ip_address' => [$errorMessage],
                    ],
                ], 422);
            }

            return back()->withErrors(['fast_connect' => $errorMessage]);
        }

        $bridgeMessage = __(
            'The remote gateway (websockify) is not running. Start it on the server with: php artisan vnc:bridge --daemon',
        );

        if (config('vnc.bridge_check') && ! $this->sessions->isBridgeUp()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $bridgeMessage], 503);
            }

            return back()->withErrors(['fast_connect' => $bridgeMessage]);
        }

        $vncSocket = @fsockopen($ipAddress, $vncPort, $vncErrno, $vncErrstr, 2.0);
        if (! is_resource($vncSocket)) {
            $errLower = strtolower($vncErrstr ?: '');
            if ($vncErrno === 111 || str_contains($errLower, 'refused')) {
                $unreachableMessage = "VNC PORT CLOSED: VNC service on \"{$ipAddress}:{$vncPort}\" is refused. Ensure VNC server is running on target.";
            } elseif ($vncErrno === 110 || str_contains($errLower, 'timed out')) {
                $unreachableMessage = "CONNECTION TIMEOUT: \"{$ipAddress}:{$vncPort}\" did not respond. Check network or IP address.";
            } else {
                $unreachableMessage = "\"{$ipAddress}\" is unreachable on port {$vncPort} — " . ($vncErrstr ?: 'Failed to open remote session.');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $unreachableMessage], 503);
            }

            return back()->withErrors(['fast_connect' => $unreachableMessage]);
        } else {
            fclose($vncSocket);
        }

        if ($saveDevice) {
            Computer::create([
                'name' => $deviceName,
                'ip_address' => $ipAddress,
                'vnc_port' => $vncPort,
                'vnc_password' => $vncPassword,
                'os_type' => $osType,
                'location' => 'Fast Connect',
                'description' => 'Auto-added via Fast Connect',
            ]);
        }

        $token = $this->sessions->createDirectSession($ipAddress, $vncPort, $vncPassword, $deviceName, $osType);

        AuditLogger::log('vnc.fast_connect', "Opened Fast Connect VNC session to {$ipAddress}:{$vncPort}", [
            'ip_address' => $ipAddress,
            'vnc_port' => $vncPort,
            'os_type' => $osType,
            'saved_as_device' => $saveDevice,
            'session_token' => substr($token, 0, 10) . '...',
        ]);

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
            'ip_address' => $session['ip_address'] ?? '',
            'vnc_port' => $session['vnc_port'] ?? 5900,
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
