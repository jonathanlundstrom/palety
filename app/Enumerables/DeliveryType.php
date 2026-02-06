<?php

namespace App\Enumerables;

use App\Enumerables\Interfaces\ColoredEnum;
use App\Enumerables\Traits\EnumHelpers;

/**
 * This enum represents the type of delivery.
 */
enum DeliveryType implements ColoredEnum {
    use EnumHelpers;

    case SELF_PICKUP;
    case ADDRESS_DELIVERY;
    case NOVA_POSHTA_DELIVERY;

    /**
     * Determines if the current instance represents a delivery type.
     * @return bool
     */
    public function isDelivery(): bool {
        return in_array($this, [
            self::ADDRESS_DELIVERY,
            self::NOVA_POSHTA_DELIVERY,
        ]);
    }

    /**
     * Determines if the current instance should have an associated address.
     * @return bool
     */
    public function hasAddress(): bool {
        return in_array($this, [
            self::ADDRESS_DELIVERY,
        ]);
    }

    /**
     * Return the color associated with the current case.
     *
     * @return string
     */
    public function color(): string {
        return match($this) {
            self::SELF_PICKUP => 'yellow',
            self::ADDRESS_DELIVERY => 'orange',
            self::NOVA_POSHTA_DELIVERY => 'red',
        };
    }
}
