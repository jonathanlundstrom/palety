<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of recipient.
 */
enum RecipientType {
    use EnumHelpers;

    case INDIVIDUAL;
    case ORGANISATION;

    /**
     * Determines if the current instance represents a legal entity.
     * @return bool
     */
    public function isLegalEntity(): bool {
        return in_array($this, [
            self::ORGANISATION,
        ]);
    }
}
