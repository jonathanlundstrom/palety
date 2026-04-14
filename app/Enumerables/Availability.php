<?php

namespace App\Enumerables;

use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the availability status of a parcel or pallet.
 * For instance, is this parcel or pallet free to be loaded or not?
 */
enum Availability {
    use EnumHelpers;

    case ANY_STATUS;
    case AVAILABLE;
    case ALREADY_LOADED;
    case LOADED_ON_PALLET;
    case LOADED_ON_TRANSPORT;

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::AVAILABLE => 'lime',
            self::ALREADY_LOADED => 'red',
            self::LOADED_ON_PALLET => 'red',
            self::LOADED_ON_TRANSPORT => 'red',
            default => 'zinc',
        };
    }

    /**
     * Get all the valid cases for filtering parcels based on availability.
     *
     * @return Availability[]
     */
    public static function parcelFilters(): array {
        return array_filter(self::cases(), fn($case) => $case !== self::AVAILABLE);
    }

    /**
     * Get all the valid cases for filtering pallets based on availability.
     *
     * @return Availability[]
     */
    public static function palletFilters(): array {
        return [
            self::ANY_STATUS,
            self::LOADED_ON_TRANSPORT,
        ];
    }
}
