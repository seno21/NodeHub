<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * Display a listing of the tags.
     */
    public function index(): View
    {
        $tags = Tag::withCount('computers')->latest()->get();

        return view('tags.index', [
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created tag in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tags,name'],
            'color' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $tag = Tag::query()->create($validated);

        AuditLogger::log('tag.create', "Membuat tag baru: {$tag->name}", [
            'tag_id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
        ]);

        return redirect()->route('tags.index')
            ->with('status', __('Tag baru berhasil dibuat!'));
    }

    /**
     * Update the specified tag in storage.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('tags', 'name')->ignore($tag->id)],
            'color' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $tag->update($validated);

        AuditLogger::log('tag.update', "Memperbarui tag: {$tag->name}", [
            'tag_id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
        ]);

        return redirect()->route('tags.index')
            ->with('status', __('Tag berhasil diperbarui!'));
    }

    /**
     * Remove the specified tag from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $name = $tag->name;
        $tag->delete();

        AuditLogger::log('tag.delete', "Menghapus tag: {$name}", [
            'name' => $name,
        ]);

        return redirect()->route('tags.index')
            ->with('status', __('Tag berhasil dihapus.'));
    }
}
