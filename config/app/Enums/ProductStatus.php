<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Archived = 'archived';
}
