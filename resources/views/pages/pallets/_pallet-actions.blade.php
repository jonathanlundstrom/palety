<?php

use App\Enumerables\Permission;

?>
<x-item-actions>
    @unless ($item->isLocked())
        @can(Permission::EDIT_PALLET)
            <x-actions.edit-button :form="$this->modalName" :object="$item"/>
        @endcan
    @endunless

    @can(Permission::CREATE_PALLET)
        <x-actions.duplicate-button :form="$this->modalName" :object="$item"/>
    @endcan

    @unless ($item->isLocked())
        @can(Permission::PRINT_LABELS)
            <x-actions.print-button :object="$item"/>
        @endcan

        @can(Permission::EDIT_TRANSPORT)
            <x-actions.add-to-transport-button :object="$item"/>
        @endcan
    @endunless

    @can(Permission::DELETE_PALLET)
        <x-actions.delete-button :object="$item" :disabled="$item->isLocked()"/>
    @endcan
</x-item-actions>
