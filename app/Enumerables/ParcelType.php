<?php

namespace App\Enumerables;

/**
 * This enum represents the type of the parcel
 * For instance, is this a box, a bag, or something else.
 */
enum ParcelType {
    case BOX;
    case OTHER;
}
