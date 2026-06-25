<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\SessionRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\SessionRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function __invoke(Request $request, SessionRegistration $sessionRegistration): JsonResponse
    {
        $trainerProfile = $request->user()->trainerProfile;
        $session = $sessionRegistration->drawingSession;

        if (!$trainerProfile || $session->trainer_profile_id !== $trainerProfile->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'status' => ['required', Rule::enum(SessionRegistrationStatus::class)],
        ]);

        $sessionRegistration->status = SessionRegistrationStatus::from($request->input('status'));
        $sessionRegistration->save();

        return response()->json([
            'success' => true,
            'status' => $sessionRegistration->status->value,
            'message' => 'Attendance status updated.',
        ]);
    }
}
