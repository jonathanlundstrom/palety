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
}
