<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $trainerProfile = $request->user()->trainerProfile;

        if (!$trainerProfile) {
            abort(403, 'User does not have an active trainer profile.');
        }

        $sessions = $trainerProfile->drawingSessions()
            ->with('category:id,name,slug')
            ->orderBy('starts_at', 'desc')
            ->get()
            ->map(fn ($session): array => [
                'id' => $session->id,
                'title' => $session->title,
                'slug' => $session->slug,
                'starts_at' => $session->starts_at?->toIso8601String(),
                'ends_at' => $session->ends_at?->toIso8601String(),
                'status' => $session->status->value,
                'capacity' => $session->capacity,
                'registered_count' => $session->registered_count,
                'category_name' => $session->category?->name,
                'trainer_response_note' => $session->trainer_response_note,
                'trainer_responded_at' => $session->trainer_responded_at?->toIso8601String(),
            ]);

        return Inertia::render('Trainer/Dashboard', [
            'sessions' => $sessions,
        ]);
    }
}
