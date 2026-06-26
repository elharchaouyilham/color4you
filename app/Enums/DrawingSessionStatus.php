<?php

namespace App\Enums;

enum DrawingSessionStatus: string
{
    case Draft = 'draft';
    case PendingTrainer = 'pending_trainer';
    case TrainerRefused = 'trainer_refused';
    case Open = 'open';
    case Full = 'full';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
