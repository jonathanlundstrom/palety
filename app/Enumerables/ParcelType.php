<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of the parcel
 * For instance, is this a box, a bag, or something else.
 */
enum ParcelType {
    use EnumHelpers;

    case BOX;
    case OTHER;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::BOX => 'lime',
            self::OTHER => 'blue',
        };
    }
}
