<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\RemoteAction;
use App\Services\AuditLogger;
use App\Services\RemoteActionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoteActionController extends Controller
{
    public function __construct(private RemoteActionService $actionService) {}

    /**
     * Display the Remote Actions dashboard.
     */
    public function index(): View
    {
        $actions = RemoteAction::with('computers')->latest()->get();
        $computers = Computer::query()->latest()->get();

        return view('actions.index', [
            'actions' => $actions,
            'computers' => $computers,
        ]);
    }

    /**
     * Show the form for creating a new remote action.
     */
    public function create(): View
    {
        $query = Computer::query();

        if (\Illuminate\Support\Facades\Schema::hasTable('tags')) {
            $query->with('tagsRelation');
        }

        $allTags = \Illuminate\Support\Facades\Schema::hasTable('tags')
            ? \App\Models\Tag::query()->orderBy('name')->get()
            : collect();

        return view('actions.create', [
            'computers' => $query->latest()->get(),
            'allTags' => $allTags,
        ]);
    }

    /**
     * Store a newly created remote action.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'command' => ['required', 'string', 'max:2000'],
            'computer_ids' => ['required', 'array', 'min:1'],
            'computer_ids.*' => ['integer', 'exists:computers,id'],
        ]);

        $action = RemoteAction::query()->create([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'description' => $validated['description'] ?? null,
            'command' => $validated['command'],
        ]);

        $action->computers()->sync($validated['computer_ids']);

        AuditLogger::log('action.create', "Membuat aksi remote baru: {$action->name}", [
            'action_id' => $action->id,
            'name' => $action->name,
            'command' => $action->command,
            'target_count' => count($validated['computer_ids']),
        ]);

        return redirect()->route('actions.index')
            ->with('status', __('Aksi Remote baru berhasil dibuat!'));
    }

    /**
     * Display details / crosscheck preview of the specified remote action.
     */
    public function show(RemoteAction $action): View
    {
        $action->load('computers');

        return view('actions.show', [
            'action' => $action,
        ]);
    }

    /**
     * Show the form for editing the specified remote action.
     */
    public function edit(RemoteAction $action): View
    {
        $action->load('computers');
        $query = Computer::query();

        if (\Illuminate\Support\Facades\Schema::hasTable('tags')) {
            $query->with('tagsRelation');
        }

        $allTags = \Illuminate\Support\Facades\Schema::hasTable('tags')
            ? \App\Models\Tag::query()->orderBy('name')->get()
            : collect();

        return view('actions.edit', [
            'action' => $action,
            'computers' => $query->latest()->get(),
            'allTags' => $allTags,
        ]);
    }

    /**
     * Update the specified remote action.
     */
    public function update(Request $request, RemoteAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'command' => ['required', 'string', 'max:2000'],
            'computer_ids' => ['required', 'array', 'min:1'],
            'computer_ids.*' => ['integer', 'exists:computers,id'],
        ]);

        $action->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'description' => $validated['description'] ?? null,
            'command' => $validated['command'],
        ]);

        $action->computers()->sync($validated['computer_ids']);

        AuditLogger::log('action.update', "Memperbarui aksi remote: {$action->name}", [
            'action_id' => $action->id,
            'name' => $action->name,
            'command' => $action->command,
        ]);

        return redirect()->route('actions.index')
            ->with('status', __('Aksi Remote berhasil diperbarui!'));
    }

    /**
     * Remove the specified remote action.
     */
    public function destroy(RemoteAction $action): RedirectResponse
    {
        $name = $action->name;
        $action->delete();

        AuditLogger::log('action.delete', "Menghapus aksi remote: {$name}", [
            'name' => $name,
        ]);

        return redirect()->route('actions.index')
            ->with('status', __('Aksi Remote berhasil dihapus.'));
    }

    /**
     * Execute the specified remote action across targeted computers.
     */
    public function execute(Request $request, RemoteAction $action): JsonResponse
    {
        $targetIds = $request->input('computer_ids', []);
        $results = $this->actionService->executeAction($action, is_array($targetIds) ? $targetIds : []);

        $successCount = collect($results)->where('success', true)->count();
        $failCount = count($results) - $successCount;

        AuditLogger::log('action.execute', "Mengeksekusi aksi remote '{$action->name}' pada " . count($results) . " perangkat", [
            'action_id' => $action->id,
            'action_name' => $action->name,
            'command' => $action->command,
            'total_targets' => count($results),
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ]);

        return response()->json([
            'status' => 'completed',
            'action_name' => $action->name,
            'command' => $action->command,
            'total' => count($results),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results,
        ]);
    }
}
