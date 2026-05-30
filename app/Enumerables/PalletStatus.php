<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the status of the pallet
 * For instance, if the pallet is a draft or completed.
 */
enum PalletStatus implements ColoredEnum {
    use EnumHelpers;

    case DRAFT;
    case COMPLETED;

    /**
     * Return the color associated with the current case.
     */
    public function color(): string {
        return match ($this) {
            self::DRAFT => 'blue',
            self::COMPLETED => 'green',
        };
    }
}
