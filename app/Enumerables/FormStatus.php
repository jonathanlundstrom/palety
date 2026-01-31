<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the status of the current form.
 */
enum FormStatus {
    use EnumHelpers;

    case CREATING;
    case EDITING;
}
