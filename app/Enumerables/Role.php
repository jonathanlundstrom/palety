<?php

declare(strict_types=1);

namespace App\Enumerables;

use Shipfastlabs\Grant\Role as RoleContract;

enum Role: string implements RoleContract {
    case SUPER_ADMIN = 'super-admin';
    case WAREHOUSE_VOLUNTEER = 'warehouse-volunteer';
    case FIELD_COORDINATOR = 'field-coordinator';
    case IMPORT_AGENT = 'import-agent';

    public function permissions(): array {
        return match ($this) {
            self::SUPER_ADMIN => Permission::cases(),
            self::WAREHOUSE_VOLUNTEER => [
                Permission::VIEW_STATISTICS,
                Permission::PRINT_LABELS,
                Permission::DOWNLOAD_DECLARATIONS,

                Permission::VIEW_PARCELS,
                Permission::CREATE_PARCEL,
                Permission::EDIT_PARCEL,
                Permission::DELETE_PARCEL,

                Permission::VIEW_PALLETS,
                Permission::CREATE_PALLET,
                Permission::EDIT_PALLET,
                Permission::DELETE_PALLET,

                Permission::VIEW_TRANSPORTS,
                Permission::VIEW_ALL_TRANSPORTS,
                Permission::CREATE_TRANSPORT,
                Permission::EDIT_TRANSPORT,
            ],
            self::FIELD_COORDINATOR => [],
            self::IMPORT_AGENT => [
                Permission::VIEW_TRANSPORTS,
                Permission::DOWNLOAD_DECLARATIONS,
            ],
        };
    }

    /**
     * Return the color associated with the current case.
     */
    public function color(): string {
        return match ($this) {
            self::SUPER_ADMIN => 'blue',
            self::WAREHOUSE_VOLUNTEER => 'lime',
            self::FIELD_COORDINATOR => 'orange',
            self::IMPORT_AGENT => 'emerald',
        };
    }

    /**
     * Retrieves the label associated with the current enum case.
     *
     * @return string Returns the translated label
     */
    public function label(): string {
        return __("enumerables.{$this->name}");
    }
}
