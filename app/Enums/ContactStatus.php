<?php

namespace App\Enums;

enum ContactStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Closed = 'closed';
}
