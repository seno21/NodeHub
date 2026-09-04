<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComputerRequest;
use App\Http\Requests\UpdateComputerRequest;
use App\Models\Computer;
use App\Models\Tag;
use App\Services\AuditLogger;
use App\Services\VncSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ComputerController extends Controller
{
    public function __construct(private VncSessionService $sessions) {}

    /**
     * Dashboard overview: device counts and quick stats.
     */
    public function dashboard(): View
    {
        return view('dashboard', [
            'total' => Computer::query()->count(),
            'windowsCount' => Computer::query()->where('os_type', 'windows')->count(),
            'linuxCount' => Computer::query()->where('os_type', 'linux')->count(),
        ]);
    }

    /**
     * Display a listing of the devices.
     */
    public function index(Request $request): View
    {
        $query = Computer::query();

        if (Schema::hasTable('tags')) {
            $query->with('tagsRelation');
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('vnc_port', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->where(function ($q) use ($tag) {
                $q->whereHas('tagsRelation', function ($t) use ($tag) {
                    if (is_numeric($tag)) {
                        $t->where('tags.id', $tag);
                    } else {
                        $t->where('tags.name', $tag);
                    }
                })
                ->orWhere('tags', 'like', "%{$tag}%");
            });
        }

        if ($request->filled('os')) {
            $query->where('os_type', $request->input('os'));
        }

        $allTags = Schema::hasTable('tags') ? Tag::query()->orderBy('name')->get() : collect();
        $computers = (clone $query)->latest()->paginate(15)->withQueryString();

        $allDevices = (clone $query)->latest()->get()->map(function ($c) {
            $tagNames = [];
            if ($c->relationLoaded('tagsRelation') && $c->tagsRelation->isNotEmpty()) {
                $tagNames = $c->tagsRelation->pluck('name')->all();
            } elseif ($c->tags) {
                $tagNames = array_values(array_filter(array_map('trim', explode(',', $c->tags))));
            }

            return [
                'id' => $c->id,
                'name' => $c->name,
                'ip_address' => $c->ip_address,
                'vnc_port' => $c->vnc_port,
                'ssh_user' => $c->ssh_user ?: 'xubuntu',
                'ssh_port' => $c->ssh_port ?: 22,
                'os_type' => $c->os_type,
                'location' => $c->location,
                'description' => $c->description,
                'has_ssh' => !empty($c->ssh_password),
                'ssh_open' => false,
                'created_at' => $c->created_at?->format('d M Y, H:i') ?? '-',
                'tags' => array_values(array_unique($tagNames)),
                'tags_relation' => $c->relationLoaded('tagsRelation')
                    ? $c->tagsRelation->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color])->values()->all()
                    : [],
            ];
        })->values()->all();

        return view('computers.index', [
            'computers' => $computers,
            'allDevices' => $allDevices,
            'allTags' => $allTags,
        ]);
    }

    /**
     * Check TCP reachability of VNC and SSH ports for all devices.
     */
    public function status(): JsonResponse
    {
        $statuses = Computer::query()->get(['id', 'ip_address', 'vnc_port', 'ssh_port'])
            ->mapWithKeys(function (Computer $computer) {
                $vncOk = $this->sessions->isReachable($computer);

                $sshPort = (int) ($computer->ssh_port ?: 22);
                $sshSocket = @fsockopen($computer->ip_address, $sshPort, $errno, $errstr, 1);
                $sshOk = is_resource($sshSocket);
                if ($sshOk) {
                    fclose($sshSocket);
                }

                return [
                    $computer->id => [
                        'vnc' => $vncOk,
                        'ssh' => $sshOk,
                    ],
                ];
            });

        return response()->json($statuses);
    }

    /**
     * Check reachability and diagnostics of a single device (on-demand ping).
     */
    public function ping(Computer $computer): JsonResponse
    {
        $diag = $this->sessions->pingDiagnostics($computer);

        return response()->json($diag);
    }

    /**
     * Show the form for creating a new device.
     */
    public function create(Request $request): View
    {
        $duplicateFrom = null;
        $computer = null;

        if ($request->filled('duplicate_from')) {
            $query = Computer::query();
            if (Schema::hasTable('tags')) {
                $query->with('tagsRelation');
            }
            $duplicateFrom = $query->find($request->input('duplicate_from'));

            if ($duplicateFrom) {
                $computer = new Computer([
                    'name' => $duplicateFrom->name . ' (Copy)',
                    'ip_address' => $duplicateFrom->ip_address,
                    'os_type' => $duplicateFrom->os_type,
                    'location' => $duplicateFrom->location,
                    'vnc_port' => $duplicateFrom->vnc_port,
                    'ssh_user' => $duplicateFrom->ssh_user,
                    'ssh_port' => $duplicateFrom->ssh_port,
                    'description' => $duplicateFrom->description,
                ]);
                if ($duplicateFrom->relationLoaded('tagsRelation')) {
                    $computer->setRelation('tagsRelation', $duplicateFrom->tagsRelation);
                }
            }
        }

        return view('computers.create', [
            'computer' => $computer,
            'duplicateFrom' => $duplicateFrom,
            'allTags' => Schema::hasTable('tags') ? Tag::query()->orderBy('name')->get() : collect(),
        ]);
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(StoreComputerRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tagIds = $request->input('tag_ids', []);

        if ($request->filled('duplicate_from_id')) {
            $source = Computer::query()->find($request->input('duplicate_from_id'));
            if ($source) {
                if (empty($validated['vnc_password']) && $request->boolean('copy_vnc_password', true)) {
                    $validated['vnc_password'] = $source->vnc_password;
                }
                if (empty($validated['ssh_password']) && $request->boolean('copy_ssh_password', true)) {
                    $validated['ssh_password'] = $source->ssh_password;
                }
            }
        }

        /** @var Computer $computer */
        $computer = Computer::query()->create($validated);

        if ($request->has('tag_ids') && Schema::hasTable('tags')) {
            $computer->tagsRelation()->sync($tagIds);
            $tagNames = Tag::query()->whereIn('id', $tagIds)->pluck('name')->implode(', ');
            $computer->update(['tags' => $tagNames]);
        }

        AuditLogger::log('computer.create', "Menambahkan perangkat baru: {$computer->name} ({$computer->ip_address}:{$computer->vnc_port})", [
            'computer_id' => $computer->id,
            'name' => $computer->name,
            'ip_address' => $computer->ip_address,
            'vnc_port' => $computer->vnc_port,
            'os_type' => $computer->os_type,
            'duplicated_from' => $request->input('duplicate_from_id'),
        ]);

        return redirect()
            ->route('computers.index')
            ->with('status', $request->filled('duplicate_from_id') ? __('Perangkat berhasil diduplikasi.') : __('Device created successfully.'));
    }

    /**
     * Show the form for editing the specified device.
     */
    public function edit(Computer $computer): View
    {
        if (Schema::hasTable('tags')) {
            $computer->load('tagsRelation');
        }

        return view('computers.edit', [
            'computer' => $computer,
            'allTags' => Schema::hasTable('tags') ? Tag::query()->orderBy('name')->get() : collect(),
        ]);
    }

    /**
     * Update the specified device in storage.
     */
    public function update(UpdateComputerRequest $request, Computer $computer): RedirectResponse
    {
        $data = $request->safe()->except(['vnc_password', 'ssh_password', 'tag_ids']);
        $tagIds = $request->input('tag_ids', []);

        if ($request->filled('vnc_password')) {
            $data['vnc_password'] = $request->input('vnc_password');
        }

        if ($request->filled('ssh_password')) {
            $data['ssh_password'] = $request->input('ssh_password');
        }

        if ($request->has('tag_ids') && Schema::hasTable('tags')) {
            $computer->tagsRelation()->sync($tagIds);
            $data['tags'] = Tag::query()->whereIn('id', $tagIds)->pluck('name')->implode(', ');
        }

        $computer->update($data);

        AuditLogger::log('computer.update', "Perbarui informasi perangkat: {$computer->name}", [
            'computer_id' => $computer->id,
            'name' => $computer->name,
            'ip_address' => $computer->ip_address,
        ]);

        return redirect()
            ->route('computers.index')
            ->with('status', __('Device updated successfully.'));
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(Computer $computer): RedirectResponse
    {
        $details = [
            'computer_id' => $computer->id,
            'name' => $computer->name,
            'ip_address' => $computer->ip_address,
        ];

        $computer->delete();

        AuditLogger::log('computer.delete', "Menghapus perangkat: {$details['name']} ({$details['ip_address']})", $details);

        return redirect()
            ->route('computers.index')
            ->with('status', __('Device deleted successfully.'));
    }

    /**
     * Export all devices to JSON backup or CSV format.
     */
    public function export(Request $request)
    {
        $format = strtolower($request->input('format', 'json'));
        $includePasswords = $request->boolean('include_passwords', true);

        $query = Computer::query();
        if (Schema::hasTable('tags')) {
            $query->with('tagsRelation');
        }

        $computers = $query->get();
        $dateStr = now()->format('Y-m-d');

        if ($format === 'csv') {
            $filename = "nodehub-devices-backup-{$dateStr}.csv";
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($computers, $includePasswords) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Excel compatibility
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, [
                    'Name', 'IP Address', 'OS', 'Location',
                    'VNC Port', 'VNC Password',
                    'SSH Port', 'SSH User', 'SSH Password',
                    'Description', 'Tags'
                ]);

                foreach ($computers as $c) {
                    $tagNames = [];
                    if ($c->relationLoaded('tagsRelation') && $c->tagsRelation->isNotEmpty()) {
                        $tagNames = $c->tagsRelation->pluck('name')->all();
                    } elseif ($c->tags) {
                        $tagNames = array_values(array_filter(array_map('trim', explode(',', $c->tags))));
                    }

                    fputcsv($file, [
                        $c->name,
                        $c->ip_address,
                        $c->os_type,
                        $c->location ?: '',
                        $c->vnc_port ?: 5900,
                        $includePasswords ? ($c->vnc_password ?: '') : '',
                        $c->ssh_port ?: 22,
                        $c->ssh_user ?: 'xubuntu',
                        $includePasswords ? ($c->ssh_password ?: '') : '',
                        $c->description ?: '',
                        implode(', ', $tagNames),
                    ]);
                }

                fclose($file);
            };

            AuditLogger::log('computer.export', "Export list perangkat (Format: CSV, Count: {$computers->count()})", [
                'count' => $computers->count(),
                'format' => 'csv',
            ]);

            return response()->stream($callback, 200, $headers);
        }

        // Default JSON Backup Format
        $data = [
            'app' => 'NodeHub',
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'total_devices' => $computers->count(),
            'devices' => $computers->map(function ($c) use ($includePasswords) {
                $tagNames = [];
                if ($c->relationLoaded('tagsRelation') && $c->tagsRelation->isNotEmpty()) {
                    $tagNames = $c->tagsRelation->pluck('name')->all();
                } elseif ($c->tags) {
                    $tagNames = array_values(array_filter(array_map('trim', explode(',', $c->tags))));
                }

                return [
                    'name' => $c->name,
                    'ip_address' => $c->ip_address,
                    'os_type' => $c->os_type,
                    'location' => $c->location,
                    'vnc_port' => (int) ($c->vnc_port ?: 5900),
                    'vnc_password' => $includePasswords ? $c->vnc_password : null,
                    'ssh_port' => (int) ($c->ssh_port ?: 22),
                    'ssh_user' => $c->ssh_user ?: 'xubuntu',
                    'ssh_password' => $includePasswords ? $c->ssh_password : null,
                    'description' => $c->description,
                    'tags' => array_values(array_unique($tagNames)),
                ];
            })->all(),
        ];

        AuditLogger::log('computer.export', "Export list perangkat (Format: JSON, Count: {$computers->count()})", [
            'count' => $computers->count(),
            'format' => 'json',
        ]);

        $filename = "nodehub-devices-backup-{$dateStr}.json";
        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    /**
     * Import / Restore devices list from JSON backup or CSV file.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:10240',
            'duplicate_action' => 'required|in:skip,update,add',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $duplicateAction = $request->input('duplicate_action', 'skip');

        $devicesToImport = [];

        if ($extension === 'json') {
            $content = file_get_contents($file->getRealPath());
            $parsed = json_decode($content, true);

            if (!is_array($parsed)) {
                return back()->withErrors(['backup_file' => 'File JSON backup tidak valid atau rusak.']);
            }

            if (isset($parsed['devices']) && is_array($parsed['devices'])) {
                $devicesToImport = $parsed['devices'];
            } elseif (array_is_list($parsed)) {
                $devicesToImport = $parsed;
            } else {
                return back()->withErrors(['backup_file' => 'Format struktur file JSON backup tidak sesuai.']);
            }
        } elseif (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return back()->withErrors(['backup_file' => 'Gagal membaca file CSV.']);
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return back()->withErrors(['backup_file' => 'File CSV kosong.']);
            }

            $headerNormalized = array_map(fn ($h) => strtolower(trim(str_replace([' ', '_'], '', $h))), $header);

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 2) continue;
                $item = [];
                foreach ($row as $idx => $val) {
                    $colName = $headerNormalized[$idx] ?? null;
                    if (!$colName) continue;
                    if (str_contains($colName, 'name')) $item['name'] = trim($val);
                    elseif (str_contains($colName, 'ip')) $item['ip_address'] = trim($val);
                    elseif (str_contains($colName, 'os')) $item['os_type'] = trim($val);
                    elseif (str_contains($colName, 'location') || str_contains($colName, 'lokasi')) $item['location'] = trim($val);
                    elseif (str_contains($colName, 'vncport')) $item['vnc_port'] = (int) trim($val);
                    elseif (str_contains($colName, 'vncpass')) $item['vnc_password'] = trim($val);
                    elseif (str_contains($colName, 'sshport')) $item['ssh_port'] = (int) trim($val);
                    elseif (str_contains($colName, 'sshuser')) $item['ssh_user'] = trim($val);
                    elseif (str_contains($colName, 'sshpass')) $item['ssh_password'] = trim($val);
                    elseif (str_contains($colName, 'desc')) $item['description'] = trim($val);
                    elseif (str_contains($colName, 'tag')) $item['tags'] = array_filter(array_map('trim', explode(',', $val)));
                }
                if (!empty($item['name']) && !empty($item['ip_address'])) {
                    $devicesToImport[] = $item;
                }
            }
            fclose($handle);
        } else {
            return back()->withErrors(['backup_file' => 'Format file tidak didukung. Harap upload file .json atau .csv.']);
        }

        if (empty($devicesToImport)) {
            return back()->withErrors(['backup_file' => 'Tidak ada data perangkat yang valid ditemukan dalam file.']);
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($devicesToImport as $item) {
            $name = trim($item['name'] ?? '');
            $ip = trim($item['ip_address'] ?? '');

            if (empty($name) || empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
                $skippedCount++;
                continue;
            }

            $osType = strtolower(trim($item['os_type'] ?? 'linux'));
            if (!in_array($osType, ['windows', 'linux'])) {
                $osType = 'linux';
            }

            $vncPort = (int) ($item['vnc_port'] ?? 5900);
            if ($vncPort < 1 || $vncPort > 65535) $vncPort = 5900;

            $sshPort = (int) ($item['ssh_port'] ?? 22);
            if ($sshPort < 1 || $sshPort > 65535) $sshPort = 22;

            $sshUser = trim($item['ssh_user'] ?? 'xubuntu') ?: 'xubuntu';

            $existing = Computer::query()->where('ip_address', $ip)->first();

            if ($existing) {
                if ($duplicateAction === 'skip') {
                    $skippedCount++;
                    continue;
                }

                if ($duplicateAction === 'update') {
                    $updateData = [
                        'name' => $name,
                        'os_type' => $osType,
                        'location' => trim($item['location'] ?? '') ?: null,
                        'vnc_port' => $vncPort,
                        'ssh_port' => $sshPort,
                        'ssh_user' => $sshUser,
                        'description' => trim($item['description'] ?? '') ?: null,
                    ];
                    if (!empty($item['vnc_password'])) {
                        $updateData['vnc_password'] = $item['vnc_password'];
                    }
                    if (!empty($item['ssh_password'])) {
                        $updateData['ssh_password'] = $item['ssh_password'];
                    }

                    $existing->update($updateData);

                    $this->syncImportTags($existing, $item['tags'] ?? []);
                    $updatedCount++;
                    continue;
                }
            }

            // Create new device
            $computer = Computer::query()->create([
                'name' => $name,
                'ip_address' => $ip,
                'os_type' => $osType,
                'location' => trim($item['location'] ?? '') ?: null,
                'vnc_port' => $vncPort,
                'vnc_password' => $item['vnc_password'] ?? null,
                'ssh_port' => $sshPort,
                'ssh_user' => $sshUser,
                'ssh_password' => $item['ssh_password'] ?? null,
                'description' => trim($item['description'] ?? '') ?: null,
            ]);

            $this->syncImportTags($computer, $item['tags'] ?? []);
            $createdCount++;
        }

        AuditLogger::log('computer.import', "Import daftar perangkat dari backup ({$createdCount} dibuat, {$updatedCount} diperbarui, {$skippedCount} dilewati)", [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'duplicate_action' => $duplicateAction,
        ]);

        $statusMsg = "Proses Restore/Import Selesai! Berhasil ditambahkan: {$createdCount}, diperbarui: {$updatedCount}, dilewati: {$skippedCount}.";

        return redirect()->route('computers.index')->with('status', $statusMsg);
    }

    private function syncImportTags(Computer $computer, array|string $tagsInput): void
    {
        if (!Schema::hasTable('tags')) return;

        $rawTags = is_array($tagsInput) ? $tagsInput : explode(',', (string) $tagsInput);
        $tagNames = array_values(array_filter(array_map('trim', $rawTags)));

        if (empty($tagNames)) return;

        $tagIds = [];
        foreach ($tagNames as $name) {
            $tag = Tag::query()->firstOrCreate(['name' => $name]);
            $tagIds[] = $tag->id;
        }

        $computer->tagsRelation()->syncWithoutDetaching($tagIds);
        $computer->update(['tags' => implode(', ', $tagNames)]);
    }
}
