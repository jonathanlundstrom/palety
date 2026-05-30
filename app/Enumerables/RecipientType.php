<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of recipient.
 */
enum RecipientType implements ColoredEnum {
    use EnumHelpers;

    case INDIVIDUAL;
    case ORGANISATION;

    /**
     * Determines if the current instance represents a legal entity.
     */
    public function isLegalEntity(): bool {
        return in_array($this, [
            self::ORGANISATION,
        ]);
    }

    /**
     * Return the color associated with the current case.
     */
    public function color(): string {
        return match ($this) {
            self::INDIVIDUAL => 'lime',
            self::ORGANISATION => 'blue',
        };
    }
}
