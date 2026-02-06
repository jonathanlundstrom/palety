<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of the transport
 * For instance, if this is transport by a small vehicle, a truck, or something else.
 */
enum TransportType implements ColoredEnum{
    use EnumHelpers;

    case CAR;
    case TRUCK;
    case OTHER;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::CAR => 'lime',
            self::TRUCK => 'teal',
            self::OTHER => 'indigo',
        };
    }
}
