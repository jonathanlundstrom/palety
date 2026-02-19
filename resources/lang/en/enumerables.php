<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletType;
use App\Enumerables\Availability;
use App\Enumerables\ParcelType;
use App\Enumerables\RecipientType;
use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use App\Enumerables\UserRole;

return [
    RecipientType::ORGANISATION->name => 'Organisation',
    RecipientType::INDIVIDUAL->name => 'Individual',

    DeliveryType::SELF_PICKUP->name => 'Self-pickup',
    DeliveryType::ADDRESS_DELIVERY->name => 'Address delivery',
    DeliveryType::NOVA_POSHTA_DELIVERY->name => 'Nova Poshta delivery',

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
    PalletType::MANUAL_PALLET->name => 'Manual pallet',

    Availability::AVAILABLE->name => 'Available',
    Availability::ALREADY_LOADED->name => 'Already loaded',
    Availability::LOADED_ON_PALLET->name => 'Loaded on pallet',
    Availability::LOADED_ON_TRANSPORT->name => 'Loaded on transport',

    TransportType::CAR->name => 'Car',
    TransportType::TRUCK->name => 'Truck',
    TransportType::OTHER->name => 'Other',

    TransportStatus::IN_PROGRESS->name => 'In progress',
    TransportStatus::SENT->name => 'Has been sent',
    TransportStatus::DELIVERED->name => 'Delivered',

    UserRole::ADMIN->name => 'Admin',
    UserRole::USER->name => 'User',
];
