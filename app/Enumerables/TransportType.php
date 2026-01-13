<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of the transport
 * For instance, if this is transport by a small vehicle, a truck, or something else.
 */
enum TransportType {
    use EnumHelpers;

    case CAR;
    case TRUCK;
    case OTHER;
}
