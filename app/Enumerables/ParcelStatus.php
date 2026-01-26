<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the parcel status
 * For instance, is this parcel free to be loaded or not?
 */
enum ParcelStatus {
    use EnumHelpers;

    case AVAILABLE;
    case LOADED_ON_PALLET;
    case LOADED_ON_TRANSPORT;
    case ALREADY_LOADED;
}
