<?php

use App\Enumerables\Permission;

?>
<x-item-actions>
    @can(Permission::EDIT_PALLET)
        <x-actions.edit-button :form="$this->modalName" :object="$item"/>
    @endcan

    @can(Permission::CREATE_PALLET)
        <x-actions.duplicate-button :form="$this->modalName" :object="$item"/>
    @endcan

    @can(Permission::PRINT_LABELS)
        <x-actions.print-button :object="$item"/>
    @endcan

    @can(Permission::EDIT_TRANSPORT)
        <x-actions.add-to-transport-button :object="$item"/>
    @endcan

    @can(Permission::DELETE_PALLET)
        <x-actions.delete-button :object="$item"/>
    @endcan
</x-item-actions>
