<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the availability status of a parcel or pallet.
 * For instance, is this parcel or pallet free to be loaded or not?
 */
enum Availability {
    use EnumHelpers;

    case AVAILABLE;
    case LOADED_ON_PALLET;
    case LOADED_ON_TRANSPORT;
    case ALREADY_LOADED;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::AVAILABLE => 'lime',
            self::ALREADY_LOADED => 'red',
            default => 'zinc',
        };
    }
}
