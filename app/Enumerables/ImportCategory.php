<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum is used to categorize parcels based on their content.
 * The categories are based on the ones used in Ukrainian import declarations.
 *
 * See https://ips.ligazakon.net/document/KP230953?an=1 for full list.
 */
enum ImportCategory implements ColoredEnum {
    use EnumHelpers;

    case FOOD;
    case SANITARY_HYGIENE;
    case MEDICAL;
    case CLOTHING;
    case TECHNICAL;
    case VEHICLES;
    case FUEL;
    case OTHER;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::FOOD => 'lime',
            self::SANITARY_HYGIENE => 'cyan',
            self::MEDICAL => 'red',
            self::CLOTHING => 'emerald',
            self::TECHNICAL => 'purple',
            self::VEHICLES => 'orange',
            self::FUEL => 'yellow',
            self::OTHER => 'zinc',
        };
    }
}
