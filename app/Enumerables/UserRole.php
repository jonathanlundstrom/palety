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
}
