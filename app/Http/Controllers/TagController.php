<?php

namespace App\Http\Controllers;

use App\Models\Tag;
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

        Tag::query()->create($validated);

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

        return redirect()->route('tags.index')
            ->with('status', __('Tag berhasil diperbarui!'));
    }

    /**
     * Remove the specified tag from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('status', __('Tag berhasil dihapus.'));
    }
}
