<?php

namespace App\Enumerables;

/**
 * This enum represents the type of recipient for a delivery.
 */
enum RecipientType {
    case SELF_PICKUP;
    case ADDRESS_DELIVERY;
    case NOVA_POSHTA_DELIVERY;
}
