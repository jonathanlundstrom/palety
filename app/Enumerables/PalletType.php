<?php

namespace App\Enumerables;

/**
 * This enum represents the type of pallet used in a shipping or logistics context.
 * It can either be a calculated pallet based on the dimensions and weight of the parcels,
 * or a manual override where the user specifies the pallet content and weight manually.
 */
enum PalletType {
    case CALCULATED;
    case MANUAL_OVERRIDE;
}
