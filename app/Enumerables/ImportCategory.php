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

    /**
     * Returns the enum cases that should be displayed in charts.
     * The colors have to be hardcoded because of the way Tailwind CSS works.
     */
    public static function chartCategories(): array {
        return [
            self::FOOD->name => ['bar' => 'text-lime-400'],
            self::SANITARY_HYGIENE->name => ['bar' => 'text-cyan-400'],
            self::MEDICAL->name => ['bar' => 'text-red-400'],
            self::CLOTHING->name => ['bar' => 'text-emerald-400'],
            self::TECHNICAL->name => ['bar' => 'text-purple-400'],
            self::OTHER->name => ['bar' => 'text-zinc-400'],
        ];
    }
}
