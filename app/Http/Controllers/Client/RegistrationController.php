<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRegistrationRequest;
use App\Models\DrawingSession;
use App\Models\SessionRegistration;
use App\Services\SessionRegistrationService;
use Illuminate\Http\RedirectResponse;

class RegistrationController extends Controller
{
    public function store(
        SessionRegistrationRequest $request,
        DrawingSession $drawingSession,
        SessionRegistrationService $registrationService,
    ): RedirectResponse {
        $registrationService->register($request->user(), $drawingSession);

        return back()->with('success', 'Session registration completed.');
    }

    public function cancel(
        SessionRegistration $sessionRegistration,
        SessionRegistrationService $registrationService,
    ): RedirectResponse {
        $this->authorize('cancel', $sessionRegistration);

        $registrationService->cancel($sessionRegistration);

        return back()->with('success', 'Session registration cancelled.');
    }
}
