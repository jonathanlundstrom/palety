<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of pallet used in a shipping or logistics context.
 * It can either be a calculated pallet based on the dimensions and weight of the parcels,
 * or a manual override where the user specifies the pallet content and weight manually.
 */
enum PalletType implements ColoredEnum {
    use EnumHelpers;

    case CALCULATED;
    case MANUAL_PALLET;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::CALCULATED => 'lime',
            self::MANUAL_PALLET => 'blue',
        };
    }
}
