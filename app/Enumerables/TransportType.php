<?php

namespace App\Enumerables;

/**
 * This enum represents the type of the transport
 * For instance, if this is transport by a small vehicle, a truck, or something else.
 */
enum TransportType {
    case CAR;
    case TRUCK;
    case OTHER;
}
