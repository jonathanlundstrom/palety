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
     */
    public function color(): string {
        return match ($this) {
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
     * Returns the dashboard categories mapped to their Tailwind bg class.
     * Classes must be hardcoded so Tailwind includes them in the build.
     */
    public static function chartCategories(): array {
        return [
            self::FOOD->name => 'bg-lime-400',
            self::SANITARY_HYGIENE->name => 'bg-cyan-400',
            self::MEDICAL->name => 'bg-red-400',
            self::CLOTHING->name => 'bg-emerald-400',
            self::TECHNICAL->name => 'bg-purple-400',
            self::VEHICLES->name => 'bg-orange-400',
            self::FUEL->name => 'bg-yellow-400',
            self::OTHER->name => 'bg-zinc-400',
        ];
    }
}
