<?php

use App\Enumerables\Availability;
use App\Enumerables\DeliveryType;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletStatus;
use App\Enumerables\PalletType;
use App\Enumerables\ParcelType;
use App\Enumerables\RecipientType;
use App\Enumerables\Role;
use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;

return [
    RecipientType::ORGANISATION->name => 'Організація',
    RecipientType::INDIVIDUAL->name => 'Фізична особа',

    DeliveryType::SELF_PICKUP->name => 'Самовивіз',
    DeliveryType::ADDRESS_DELIVERY->name => 'Доставка за адресою',
    DeliveryType::NOVA_POSHTA_DELIVERY->name => 'Доставка Новою Поштою',

    ParcelType::BOX->name => 'Коробка',
    ParcelType::OTHER->name => 'Інше',

    ImportCategory::FOOD->name => 'Продовольство',
    ImportCategory::SANITARY_HYGIENE->name => 'Санітарно-гігієнічні засоби',
    ImportCategory::MEDICAL->name => 'Лікарські засоби та медичні вироби',
    ImportCategory::CLOTHING->name => 'Одяг / Взуття',
    ImportCategory::TECHNICAL->name => 'Технічні засоби',
    ImportCategory::VEHICLES->name => 'Транспортні засоби',
    ImportCategory::FUEL->name => 'Паливо',
    ImportCategory::OTHER->name => 'Інше',

    PalletType::CALCULATED->name => 'Розрахована',
    PalletType::MANUAL_PALLET->name => 'Ручна',

    Availability::ANY_STATUS->name => 'Будь-який статус',
    Availability::AVAILABLE->name => 'Доступна',
    Availability::ALREADY_LOADED->name => 'Недоступна',
    Availability::LOADED_ON_PALLET->name => 'Завантажено на палету',
    Availability::LOADED_ON_TRANSPORT->name => 'Завантажено на транспорт',

    TransportType::CAR->name => 'Автомобіль',
    TransportType::TRUCK->name => 'Вантажівка',
    TransportType::OTHER->name => 'Інше',

    TransportStatus::IN_PROGRESS->name => 'В процесі',
    TransportStatus::SENT->name => 'Відправлено',
    TransportStatus::DELIVERED->name => 'Доставлено',

    PalletStatus::DRAFT->name => 'Чернетка',
    PalletStatus::COMPLETED->name => 'Завершено',

    Role::SUPER_ADMIN->name => 'Суперадміністратор',
    Role::WAREHOUSE_VOLUNTEER->name => 'Волонтер складу',
    Role::FIELD_COORDINATOR->name => 'Польовий координатор',
    Role::IMPORT_AGENT->name => 'Агент з імпорту',
];
