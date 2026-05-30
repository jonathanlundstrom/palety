<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the status of the current form.
 */
enum UserRole {
    use EnumHelpers;

    case USER;
    case ADMIN;

    /**
     * Return the color associated with the current case.
     */
    public function color(): string {
        return match ($this) {
            self::USER => 'lime',
            self::ADMIN => 'blue',
        };
    }
}
