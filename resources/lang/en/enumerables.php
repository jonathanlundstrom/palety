<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletType;
use App\Enumerables\ParcelType;
use App\Enumerables\RecipientType;
use App\Enumerables\TransportType;

return [
    RecipientType::ORGANISATION->name => 'Organisation',
    RecipientType::INDIVIDUAL->name => 'Individual',

    DeliveryType::SELF_PICKUP->name => 'Self-pickup',
    DeliveryType::ADDRESS_DELIVERY->name => 'Address Delivery',
    DeliveryType::NOVA_POSHTA_DELIVERY->name => 'Nova Poshta Delivery',

    ParcelType::BOX->name => 'Box',
    ParcelType::OTHER->name => 'Other',

    ImportCategory::FOOD->name => 'Food',
    ImportCategory::SANITARY_HYGIENE->name => 'Sanitary and hygienic means',
    ImportCategory::MEDICAL->name => 'Medical devices and products',
    ImportCategory::CLOTHING->name => 'Clothing/Shoes',
    ImportCategory::TECHNICAL->name => 'Technical means',
    ImportCategory::VEHICLES->name => 'Vehicles',
    ImportCategory::FUEL->name => 'Fuel',
    ImportCategory::OTHER->name => 'Other',

    PalletType::CALCULATED->name => 'Automatically calculated',
    PalletType::MANUAL_OVERRIDE->name => 'Manual override',

    TransportType::CAR->name => 'Car',
    TransportType::TRUCK->name => 'Truck',
    TransportType::OTHER->name => 'Other',
];
