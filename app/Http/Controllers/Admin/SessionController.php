<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DrawingSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DrawingSession;
use App\Models\TrainerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(): Response
    {
        $sessions = DrawingSession::query()
            ->with(['category:id,name', 'trainerProfile.user:id,first_name,last_name'])
            ->orderBy('starts_at', 'desc')
            ->get()
            ->map(fn ($session): array => [
                'id' => $session->id,
                'trainer_profile_id' => $session->trainer_profile_id,
                'trainer_name' => $session->trainerProfile?->user?->name,
                'category_id' => $session->category_id,
                'category_name' => $session->category?->name,
                'title' => $session->title,
                'slug' => $session->slug,
                'description' => $session->description,
                'starts_at' => $session->starts_at?->toIso8601String(),
                'ends_at' => $session->ends_at?->toIso8601String(),
                'capacity' => $session->capacity,
                'registered_count' => $session->registered_count,
                'price' => $session->price,
                'status' => $session->status->value,
                'cover_url' => $session->getFirstMediaUrl('cover') ?: null,
                'trainer_response_note' => $session->trainer_response_note,
                'trainer_responded_at' => $session->trainer_responded_at?->toIso8601String(),
            ]);

        $trainers = TrainerProfile::query()
            ->with('user:id,first_name,last_name')
            ->where('is_active', true)
            ->get()
            ->map(fn ($t): array => [
                'id' => $t->id,
                'name' => ($t->user ? $t->user->name : 'Unknown') . ' (' . $t->specialty . ')',
            ]);

        $categories = Category::sessionCategories()->active()->get(['id', 'name']);

        return Inertia::render('Admin/Sessions/Index', [
            'sessions' => $sessions,
            'trainers' => $trainers,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trainer_profile_id' => ['required', 'exists:trainer_profiles,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(DrawingSessionStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $count = DrawingSession::query()->where('slug', $validated['slug'])->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $session = DrawingSession::create($validated);

        if ($request->hasFile('image')) {
            $session->addMediaFromRequest('image')->toMediaCollection('cover');
        }

        return back()->with('success', 'Drawing session created successfully.');
    }

    public function update(Request $request, DrawingSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'trainer_profile_id' => ['required', 'exists:trainer_profiles,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(DrawingSessionStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validated['capacity'] < $session->registered_count) {
            return back()->withErrors(['capacity' => 'Capacity cannot be less than the number of registered participants (' . $session->registered_count . ').']);
        }

        $validated['slug'] = Str::slug($validated['title']);
        $count = DrawingSession::query()->where('slug', $validated['slug'])->where('id', '!=', $session->id)->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $session->update($validated);

        if ($request->hasFile('image')) {
            $session->clearMediaCollection('cover');
            $session->addMediaFromRequest('image')->toMediaCollection('cover');
        }

        return back()->with('success', 'Drawing session updated successfully.');
    }

    public function destroy(DrawingSession $session): RedirectResponse
    {
        if ($session->registrations()->where('status', 'registered')->exists()) {
            return back()->with('error', 'Cannot delete a drawing session with active registrants.');
        }

        $session->delete();

        return back()->with('success', 'Drawing session deleted successfully.');
    }
}
