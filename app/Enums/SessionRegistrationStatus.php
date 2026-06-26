<?php

namespace App\Enums;

enum SessionRegistrationStatus: string
{
    case Registered = 'registered';
    case Cancelled = 'cancelled';
    case Attended = 'attended';
    case Absent = 'absent';
}
