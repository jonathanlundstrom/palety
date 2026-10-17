<?php

use App\Enumerables\Permission;

?>
<x-item-actions>
    @can(Permission::EDIT_RECIPIENT)
        <x-actions.edit-button :form="$this->modalName" :object="$item"/>
    @endcan

    @can(Permission::DELETE_RECIPIENT)
        <x-actions.delete-button :object="$item"/>
    @endcan
</x-item-actions>
