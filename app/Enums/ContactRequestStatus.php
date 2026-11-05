<?php

namespace App\Enums;

enum ContactRequestStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';

    /**
     * The label shown in the admin panel and the CSV export.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In progress',
            self::Done => 'Done',
        };
    }
}
