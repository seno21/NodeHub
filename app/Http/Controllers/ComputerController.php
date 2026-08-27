<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComputerRequest;
use App\Http\Requests\UpdateComputerRequest;
use App\Models\Computer;
use App\Models\Tag;
use App\Services\VncSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

use Illuminate\Http\Request;

class ComputerController extends Controller
{
    public function __construct(private VncSessionService $sessions) {}

    /**
     * Dashboard overview: device counts and quick stats.
     */
    public function dashboard(): View
    {
        $computers = Computer::query()->get(['id', 'os_type']);

        return view('dashboard', [
            'total' => $computers->count(),
            'windowsCount' => $computers->where('os_type', 'windows')->count(),
            'linuxCount' => $computers->where('os_type', 'linux')->count(),
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

            $sshPort = (int) ($c->ssh_port ?: 22);
            $sshSocket = @fsockopen($c->ip_address, $sshPort, $errno, $errstr, 1);
            $sshOpen = is_resource($sshSocket);
            if ($sshOpen) {
                fclose($sshSocket);
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
                'ssh_open' => $sshOpen,
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
    public function create(): View
    {
        return view('computers.create', [
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

        /** @var Computer $computer */
        $computer = Computer::query()->create($validated);

        if ($request->has('tag_ids') && Schema::hasTable('tags')) {
            $computer->tagsRelation()->sync($tagIds);
            $tagNames = Tag::query()->whereIn('id', $tagIds)->pluck('name')->implode(', ');
            $computer->update(['tags' => $tagNames]);
        }

        return redirect()
            ->route('computers.index')
            ->with('status', __('Device created successfully.'));
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

        return redirect()
            ->route('computers.index')
            ->with('status', __('Device updated successfully.'));
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(Computer $computer): RedirectResponse
    {
        $computer->delete();

        return redirect()
            ->route('computers.index')
            ->with('status', __('Device deleted successfully.'));
    }
}
