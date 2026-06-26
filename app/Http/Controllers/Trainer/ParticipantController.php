<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\DrawingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function __invoke(Request $request, DrawingSession $drawingSession): JsonResponse
    {
        $trainerProfile = $request->user()->trainerProfile;

        if (!$trainerProfile || $drawingSession->trainer_profile_id !== $trainerProfile->id) {
            abort(403, 'Unauthorized.');
        }

        $participants = $drawingSession->registrations()
            ->with('user:id,first_name,last_name,email,phone')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($reg): array => [
                'id' => $reg->id,
                'status' => $reg->status->value,
                'registered_at' => $reg->registered_at?->toIso8601String(),
                'user' => [
                    'id' => $reg->user?->id,
                    'name' => $reg->user?->name,
                    'email' => $reg->user?->email,
                    'phone' => $reg->user?->phone,
                ],
            ]);

        return response()->json([
            'participants' => $participants,
        ]);
    }
}
