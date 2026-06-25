<?php

namespace App\Policies;

use App\Models\SessionRegistration;
use App\Models\User;

class SessionRegistrationPolicy
{
    public function cancel(User $user, SessionRegistration $sessionRegistration): bool
    {
        return $sessionRegistration->user_id === $user->id
            && $sessionRegistration->canCancel();
    }
}
