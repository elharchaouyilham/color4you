<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\DrawingSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\DrawingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SessionResponseController extends Controller
{
    public function __invoke(Request $request, DrawingSession $drawingSession): RedirectResponse
    {
        $trainerProfile = $request->user()->trainerProfile;

        if (!$trainerProfile || $drawingSession->trainer_profile_id !== $trainerProfile->id) {
            abort(403, 'Unauthorized.');
        }

        if ($drawingSession->status !== DrawingSessionStatus::PendingTrainer) {
            throw ValidationException::withMessages([
                'session' => 'This session cannot be responded to.',
            ]);
        }

        $request->validate([
            'response' => ['required', 'in:confirm,refuse'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $response = $request->input('response');

        if ($response === 'confirm') {
            $drawingSession->status = DrawingSessionStatus::Open;
        } else {
            $drawingSession->status = DrawingSessionStatus::TrainerRefused;
        }

        $drawingSession->trainer_response_note = $request->input('note');
        $drawingSession->trainer_responded_at = now();
        $drawingSession->save();

        return back()->with('success', 'Your response has been submitted.');
    }
}
