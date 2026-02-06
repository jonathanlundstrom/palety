<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the status of the transport
 * For instance, if this transport is pending, sent, or delivered.
 */
enum TransportStatus {
    use EnumHelpers;

    case IN_PROGRESS;
    case SENT;
    case DELIVERED;
}
