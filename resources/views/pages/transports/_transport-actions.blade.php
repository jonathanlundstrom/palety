<?php

use App\Enumerables\Permission;

?>
<x-item-actions>
    @can(Permission::EDIT_TRANSPORT, $item)
        <x-actions.edit-button :form="$this->modalName" :object="$item" :disabled="$item->isLocked() && auth()->user()->cannot(Permission::UNLOCK_TRANSPORT)"/>
        <x-actions.delivered-button :object="$item"/>
    @endcan

    @can(Permission::DOWNLOAD_DECLARATIONS)
        <x-actions.packing-list-button :object="$item"/>
        <x-actions.import-list-button :object="$item"/>
    @endcan

    @can(Permission::DELETE_TRANSPORT, $item)
        <x-actions.delete-button :object="$item" :disabled="$item->isLocked() && auth()->user()->cannot(Permission::UNLOCK_TRANSPORT)"/>
    @endcan
</x-item-actions>
