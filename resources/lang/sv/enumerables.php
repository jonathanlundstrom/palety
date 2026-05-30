<?php

use App\Enumerables\Availability;
use App\Enumerables\DeliveryType;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletStatus;
use App\Enumerables\PalletType;
use App\Enumerables\ParcelType;
use App\Enumerables\RecipientType;
use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use App\Enumerables\UserRole;

return [
    RecipientType::ORGANISATION->name => 'Organisation',
    RecipientType::INDIVIDUAL->name => 'Privatperson',

    DeliveryType::SELF_PICKUP->name => 'Upphämtning',
    DeliveryType::ADDRESS_DELIVERY->name => 'Adressleverans',
    DeliveryType::NOVA_POSHTA_DELIVERY->name => 'Nova Poshta-leverans',

    ParcelType::BOX->name => 'Låda',
    ParcelType::OTHER->name => 'Övrigt',

    ImportCategory::FOOD->name => 'Mat',
    ImportCategory::SANITARY_HYGIENE->name => 'Sanitet och hygienartiklar',
    ImportCategory::MEDICAL->name => 'Medicinska anordningar och produkter',
    ImportCategory::CLOTHING->name => 'Kläder/Skor',
    ImportCategory::TECHNICAL->name => 'Tekniska hjälpmedel',
    ImportCategory::VEHICLES->name => 'Fordon',
    ImportCategory::FUEL->name => 'Bränsle',
    ImportCategory::OTHER->name => 'Övrigt',

    PalletType::CALCULATED->name => 'Beräknad',
    PalletType::MANUAL_PALLET->name => 'Manuell',

    Availability::ANY_STATUS->name => 'Alla statusar',
    Availability::AVAILABLE->name => 'Tillgänglig',
    Availability::ALREADY_LOADED->name => 'Ej tillgänglig',
    Availability::LOADED_ON_PALLET->name => 'Lastad på pall',
    Availability::LOADED_ON_TRANSPORT->name => 'Lastad på transport',

    TransportType::CAR->name => 'Bil',
    TransportType::TRUCK->name => 'Lastbil',
    TransportType::OTHER->name => 'Övrigt',

    TransportStatus::IN_PROGRESS->name => 'Pågående',
    TransportStatus::SENT->name => 'Har skickats',
    TransportStatus::DELIVERED->name => 'Levererad',

    PalletStatus::DRAFT->name => 'Utkast',
    PalletStatus::COMPLETED->name => 'Slutförd',

    UserRole::ADMIN->name => 'Admin',
    UserRole::USER->name => 'Användare',
];
