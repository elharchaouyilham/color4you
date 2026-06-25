<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case PickedUp = 'picked_up';
    case Returned = 'returned';
    case Expired = 'expired';
}
