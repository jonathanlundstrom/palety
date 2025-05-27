<?php

namespace App\Enumerables;

/**
 * This enum is used to categorize parcels based on their content.
 * The categories are based on the ones used in Ukrainian import declarations.
 *
 * See https://ips.ligazakon.net/document/KP230953?an=1 for full list.
 */
enum ParcelType {
    case FOOD;
    case SANITARY_HYGIENE;
    case MEDICAL;
    case CLOTHING;
    case TECHNICAL;
    case FUEL;
    case OTHER;
}
