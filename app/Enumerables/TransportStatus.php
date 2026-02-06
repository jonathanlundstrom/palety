<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the status of the transport
 * For instance, if this transport is pending, sent, or delivered.
 */
enum TransportStatus implements ColoredEnum{
    use EnumHelpers;

    case IN_PROGRESS;
    case SENT;
    case DELIVERED;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::IN_PROGRESS => 'blue',
            self::SENT => 'yellow',
            self::DELIVERED => 'green',
        };
    }
}
