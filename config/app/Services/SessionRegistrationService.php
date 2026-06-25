<?php

namespace App\Services;

use App\Enums\DrawingSessionStatus;
use App\Enums\SessionRegistrationStatus;
use App\Models\DrawingSession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SessionRegistrationService
{
    public function register(User $user, DrawingSession $drawingSession): SessionRegistration
    {
        return DB::transaction(function () use ($user, $drawingSession) {
            $drawingSession = DrawingSession::query()
                ->lockForUpdate()
                ->findOrFail($drawingSession->id);

            if (!in_array($drawingSession->status, [DrawingSessionStatus::Open, DrawingSessionStatus::Full], true)) {
                throw ValidationException::withMessages([
                    'session' => 'This session is not open for registration.',
                ]);
            }

            if ($drawingSession->registered_count >= $drawingSession->capacity) {
                throw ValidationException::withMessages([
                    'session' => 'This session has reached capacity.',
                ]);
            }

            $existingRegistration = SessionRegistration::query()
                ->where('user_id', $user->id)
                ->where('drawing_session_id', $drawingSession->id)
                ->first();

            if ($existingRegistration !== null) {
                if ($existingRegistration->status === SessionRegistrationStatus::Cancelled) {
                    $existingRegistration->status = SessionRegistrationStatus::Registered;
                    $existingRegistration->cancelled_at = null;
                    $existingRegistration->registered_at = now();
                    $existingRegistration->save();

                    $drawingSession->registered_count++;

                    if ($drawingSession->registered_count >= $drawingSession->capacity) {
                        $drawingSession->status = DrawingSessionStatus::Full;
                    }

                    $drawingSession->save();

                    return $existingRegistration;
                }

                throw ValidationException::withMessages([
                    'session' => 'You are already registered for this session.',
                ]);
            }

            $registration = SessionRegistration::query()->create([
                'user_id' => $user->id,
                'drawing_session_id' => $drawingSession->id,
                'registered_at' => now(),
                'status' => SessionRegistrationStatus::Registered,
            ]);

            $drawingSession->registered_count++;

            if ($drawingSession->registered_count >= $drawingSession->capacity) {
                $drawingSession->status = DrawingSessionStatus::Full;
            }

            $drawingSession->save();

            return $registration;
        });
    }

    public function cancel(SessionRegistration $sessionRegistration): SessionRegistration
    {
        return DB::transaction(function () use ($sessionRegistration) {
            $sessionRegistration = SessionRegistration::query()
                ->with('drawingSession')
                ->lockForUpdate()
                ->findOrFail($sessionRegistration->id);

            if (!$sessionRegistration->canCancel()) {
                throw ValidationException::withMessages([
                    'registration' => 'This registration can no longer be cancelled.',
                ]);
            }

            $session = DrawingSession::query()
                ->lockForUpdate()
                ->findOrFail($sessionRegistration->drawing_session_id);

            $sessionRegistration->status = SessionRegistrationStatus::Cancelled;
            $sessionRegistration->cancelled_at = now();
            $sessionRegistration->save();

            $session->registered_count = max(0, $session->registered_count - 1);

            if (
                $session->status === DrawingSessionStatus::Full
                && $session->registered_count < $session->capacity
            ) {
                $session->status = DrawingSessionStatus::Open;
            }

            $session->save();

            return $sessionRegistration;
        });
    }
}
