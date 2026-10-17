<?php

declare(strict_types=1);

namespace App\Enumerables;

use Shipfastlabs\Grant\Ability;

enum Permission: string implements Ability {
    /**
     * Global permissions
     */
    case VIEW_STATISTICS = 'view-statistics';
    case PRINT_LABELS = 'print-labels';
    case DOWNLOAD_DECLARATIONS = 'download-declarations';

    /**
     * Parcel permissions
     */
    case VIEW_PARCELS = 'view-parcels';
    case CREATE_PARCEL = 'create-parcel';
    case EDIT_PARCEL = 'edit-parcel';
    case DELETE_PARCEL = 'delete-parcel';

    /**
     * Pallet permissions
     */
    case VIEW_PALLETS = 'view-pallets';
    case CREATE_PALLET = 'create-pallet';
    case EDIT_PALLET = 'edit-pallet';
    case DELETE_PALLET = 'delete-pallet';

    /**
     * Transport permissions
     */
    case VIEW_TRANSPORTS = 'view-transports';
    case CREATE_TRANSPORT = 'create-transport';
    case EDIT_TRANSPORT = 'edit-transport';
    case DELETE_TRANSPORT = 'delete-transport';
    case UNLOCK_TRANSPORT = 'unlock-transport';

    /**
     * Content permissions
     */
    case VIEW_CONTENT = 'view-content';
    case CREATE_CONTENT = 'create-content';
    case EDIT_CONTENT = 'edit-content';
    case MERGE_CONTENT = 'merge-content';
    case DELETE_CONTENT = 'delete-content';

    /**
     * Recipient permissions
     */
    case VIEW_RECIPIENTS = 'view-recipients';
    case CREATE_RECIPIENT = 'create-recipient';
    case EDIT_RECIPIENT = 'edit-recipient';
    case DELETE_RECIPIENT = 'delete-recipient';

    /**
     * User permissions
     */
    case VIEW_USERS = 'view-users';
    case CREATE_USER = 'create-user';
    case EDIT_USER = 'edit-user';
    case DELETE_USER = 'delete-user';
    case MANAGE_SUPER_ADMINS = 'manage-super-admins';

    public function deniedMessage(): string {
        return __('auth.unauthorized');
    }
}
