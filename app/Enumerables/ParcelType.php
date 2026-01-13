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
}
